<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
	private $countryArray;
	private $ifEdit;

	public function __construct($countryArray, $ifEdit)
	{
		$this->countryArray = $countryArray;
		$this->ifEdit = $ifEdit;
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$countryArray = $this->countryArray;

        $builder
            ->add('username', 'text', array(
                'constraints' => new Assert\NotBlank(), 'label' => 'Pseudo'
            ))

            ->add('email', 'email', array(
                'constraints' => new Assert\NotBlank(), 'label' => 'Email'
            ))

			->add('avatar', 'file', array(
                'data_class' => null, 'label' => 'Avatar', 'required' => false
            ))

			->add('gravatar', 'hidden', array(
                'label' => 'Avatar', 'required' => false
            ))
			
			->add('presentation', 'textarea', array(
                'constraints' => new Assert\NotBlank(), 'label' => 'Présentation'
            ))
			
			->add('country', 'choice', array(
											'label' => 'Pays', 
											'multiple' => false, 
											'expanded' => false,
											'constraints' => array(new Assert\NotBlank()),
											'empty_value' => 'Choisissez une option',
										    'choices' => $countryArray
											))
			
			
            ->add('save', 'submit', array('label' => 'Sauvegarder', "attr" => array("class" => "btn btn-success")));
			
		if(!$this->ifEdit)
		{
			$builder
				->add('password', 'repeated', array(
					'label' => 'Mot de passe',
					'type' => 'password',
					'invalid_message' => 'Les mots de passe doivent correspondre',
					'constraints' => new Assert\NotBlank(),
					'options' => array('required' => true),
					'first_options'  => array('label' => 'Mot de passe'),
					'second_options' => array('label' => 'Mot de passe (validation)'),
				))
				->add('captcha', 'text', array('label' => 'Recopiez le mot contenu dans l\'image', "mapped" => false, "attr" => array("class" => "captcha_word"), 'constraints' => new Assert\NotBlank()))
			;
		}
    }

    public function getName()
    {
        return 'user';
    }
}