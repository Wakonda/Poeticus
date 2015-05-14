<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', array(
                'constraints' => new Assert\NotBlank(), 'label' => 'Pseudo'
            ))
			->add('password', 'password', array(
                'constraints' => new Assert\NotBlank(), 'attr' => array('class' => 'redactor'), 'label' => 'Texte'
            ))
			->add('login', 'submit', array(
                'label' => 'Connectez-vous',
				'attr' => array('class' => 'btn btn-info')
            ))
    }

    public function getName()
    {
        return 'login';
    }
}
