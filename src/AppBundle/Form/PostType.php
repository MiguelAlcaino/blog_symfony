<?php

namespace AppBundle\Form;

use AppBundle\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)// se contruye los campos del formulario
    {
        $builder
            ->add('title')
            ->add('body');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)//se define la entidad del formulario
    {
        $resolver->setDefaults(array(
            'data_class' => Post::class//'AppBundle\Entity\Post'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_post';
    }


}
