<?php
	/*
	 * This file is part of the Trinity project.
	 *
	 */
	namespace Trinity\NotificationBundle\Services;


	use Doctrine\ORM\PersistentCollection;
	use GuzzleHttp\Client;
	use ReflectionClass;
	use Symfony\Component\Config\Definition\Exception\Exception;
	use Symfony\Component\EventDispatcher\EventDispatcher;
	use Symfony\Component\HttpKernel\Exception\HttpException;
	use Trinity\NotificationBundle\Entity\IEntityNotification;
	use Trinity\NotificationBundle\Notification\Annotations\NotificationProcessor;
	use Trinity\NotificationBundle\Event\Events;
	use Trinity\NotificationBundle\Event\SendEvent;
	use Trinity\NotificationBundle\Event\StatusEvent;
	use Trinity\NotificationBundle\Exception\ClientException;
	use Trinity\NotificationBundle\Exception\MethodException;
	use Nette\Utils\Strings;


	class NotificationManager {
		const DELETE = 'DELETE';
		const POST = 'POST';
		const PUT = 'PUT';


		/** @var  NotificationProcessor */
		protected $processor;

		/** @var  EventDispatcher */
		protected $eventDispatcher;


		function __construct(
			$eventDispatcher,
			NotificationProcessor $annotationProcessor
		) {
			$this->eventDispatcher = $eventDispatcher;
			$this->processor       = $annotationProcessor;
		}


		/**
		 *  Send notification to client (App).
		 *
		 *
		 * @param Object $entity
		 * @param string $method
		 *
		 * @return mixed|string|void
		 * @throws ClientException
		 * @throws MethodException
		 */
		public function send( $entity, $method = "GET" ) {
			$response = "";


			// before send event
			$this->eventDispatcher->dispatch(
				Events::BEFORE_NOTIFICATION_SEND,
				new SendEvent( $entity )
			);

			$clients = $this->clientsToArray( $entity );


			if ( ! $clients ) {
				throw new Exception("Client/s has not found.");
			}


			foreach ( $clients as $client ) {


				if ( ! $client->isNotificationEnabled() ) {
					continue;
				}


				$url  = $this->prepareURLs( $client->getNotifyUrl(), $entity, $method );
				$json = $this->json_encode_object( $entity, $client->getSecret() );

				try {
					$response = $this->createJsonRequest( $json, $url, $method, true );
					$this->eventDispatcher->dispatch(
						Events::SUCCESS_NOTIFICATION,
						new StatusEvent( $client, $entity, $entity->getId(), $url, $json, $method, null, null )
					);

				} catch ( \Exception $ex ) {
					$message = "$method: URL: " . $url . " returns error: " . $ex->getMessage() . ".";

					$this->eventDispatcher->dispatch( Events::ERROR_NOTIFICATION,
						new StatusEvent( $client, $entity, $entity->getId(), $url, $json, $method, $ex, $message ) );

					$response = "ERROR - $message";
				}
			}

			$this->eventDispatcher->dispatch(
				Events::AFTER_NOTIFICATION_SEND,
				new SendEvent( $entity )
			);

			return $response;
		}


		/**
		 * Transform clients collection to array.
		 *
		 * @param $entity
		 *
		 * @return NULL|\Object[]
		 * @throws ClientException
		 */
		protected function clientsToArray( $entity ) {
			$class = new ReflectionClass($entity);

			if(!$class->hasMethod("getClients")){
				throw new ClientException("Entity has no method 'getClass'");
			}

			$clients = $entity->getClients();


			if ( ! $clients ) {
				return null;
			} elseif ( $clients instanceof PersistentCollection ) {
				$clients = $clients->toArray();

				return $clients;
			} elseif ( ! is_array( $clients ) ) {

				$cl        = $clients;
				$clients   = [ ];
				$clients[] = $cl;

				return $clients;
			}

			return $clients;
		}


		/**
		 * @param string $url
		 * @param $entity
		 * @param $method
		 *
		 * @return array
		 * @throws ClientException
		 * @throws MethodException
		 * @internal param $client
		 * @internal param $entity
		 * @internal param $method
		 * @internal param $url
		 */
		private function prepareURLs( $url, $entity, $method ) {
			$clientMethod = "getClients";
			if ( ! is_callable( [ $entity, $clientMethod ] ) ) {
				throw new MethodException( "Method '$clientMethod' not exists in entity." );
			}

			if ( $url == null || empty( $url ) ) {
				throw new ClientException( "Notification error: Client has not set notification URL. Please use IEntityNotification." );
			}

			$class = $this->processor->getUrlPostfix( $entity, $method );
			// add / to url
			if ( ! Strings::endsWith( $url, "/" ) ) {
				$url .= "/";
			}

			return $url . $class;
		}


		/**
		 * Returns object encoded in json.
		 * Encode only first level (FK are expressed as ID strings)
		 *
		 * @param $data object
		 * @param $secret
		 *
		 * @return string
		 * @internal param string $hash
		 */
		private function json_encode_object( $data, $secret ) {
			$result              = $this->processor->convertJson( $data );
			$result['timestamp'] = ( new \DateTime() )->getTimestamp();
			$result['hash']      = hash( "sha256", $secret . ( implode( ",", $result ) ) );

			return json_encode( $result );
		}


		/**
		 * @param $data
		 * @param $url
		 * @param string $method
		 * @param bool $is_encoded
		 * @param null $secret
		 * @param int $pass
		 *
		 * @return mixed
		 */
		private function createJsonRequest(
			$data,
			$url,
			$method = self::POST,
			$is_encoded = false,
			$secret = null,
			$pass = 1
		) {
			if ( ! $is_encoded ) {
				$data = is_object( $data ) ? $this->json_encode_object( $data, $secret ) : json_encode( $data );
			}

			$client  = new Client();
			$request = $client->createRequest( $method, $url, [
				'headers' => [ 'Content-type' => 'application/json' ],
				'body'    => $data,
				'future'  => true
			] );

			/** @var \GuzzleHttp\Message\FutureResponse $response */
			$response = $client->send( $request );

			if ( ! $response || ! $response->getStatusCode() || $response->getStatusCode() != '200' ) {
				if ( $pass < 5 ) {
					sleep( rand( 1, 10 ) );

					return $this->createJsonRequest( $data, $url, $pass ++, true );
				} else {
					throw new HttpException( $response->getStatusCode(), "Notification error. {$response->getBody()}" );
				}
			} else {
				return $response->json();
            }
        }

    }