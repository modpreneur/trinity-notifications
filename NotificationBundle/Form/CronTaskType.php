<?php

namespace Trinity\NotificationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CronTaskType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @todo command collection - js for add
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', [
                'label' => 'Name'
            ])
            ->add('command', 'collection', [
                'allow_add' => true,

            ])
            ->add('delay', 'integer')
            ->add('created', 'datetime')
            ->add('save', 'submit');
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Trinity\NotificationBundle\Entity\CronTask'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'necktie_notificationbundle_crontask';
    }
}
