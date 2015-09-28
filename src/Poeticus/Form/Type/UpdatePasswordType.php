<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UpdatePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('password', 'repeated', array(
				'label' => 'Nouveau mot de passe',
				'type' => 'password',
				'invalid_message' => 'Les mots de passe doivent correspondre',
				'options' => array('required' => true),
				'first_options'  => array('label' => 'Mot de passe'),
				'second_options' => array('label' => 'Mot de passe (validation)'),
			))
			
            ->add('save', 'submit', array('label' => 'Sauvegarder'));
    }

    public function getName()
    {
        return 'updatepassword';
    }
}
