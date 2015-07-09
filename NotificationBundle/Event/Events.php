<?php
	/*
	 * This file is part of the Trinity project.
	 *
	 */

	namespace Trinity\NotificationBundle\Event;

	/**
	 * Class StoreEvents
	 * @author Tomáš Jančar
	 *
	 * @package Trinity\NotificationBundle\Event
	 */
	final class Events {

		const BEFORE_NOTIFICATION_SEND = "notification.beforeNotificationSend";

		const AFTER_NOTIFICATION_SEND = "notification.afterNotificationSend";

		const ERROR_NOTIFICATION = "notification.error";

		const SUCCESS_NOTIFICATION = "notification.success";

	}