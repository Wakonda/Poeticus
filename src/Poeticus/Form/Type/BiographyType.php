<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class BiographyType extends AbstractType
{
	private $countryArray;

	public function __construct($countryArray)
	{
		$this->countryArray = $countryArray;
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$countryArray = $this->countryArray;

        $builder
            ->add('title', 'text', array(
                'constraints' => new Assert\NotBlank(), "label" => "Titre"
            ))
			->add('text', 'textarea', array(
                'constraints' => new Assert\NotBlank(), "label" => "Texte", 'attr' => array('class' => 'redactor')
            ))
			->add('photo', 'file', array('data_class' => null, "label" => "Photo", "required" => false))
			->add('dayBirth', 'integer', array("label" => "Date de naissance", "required" => false))
			->add('monthBirth', 'integer', array("label" => "", "required" => false))
			->add('yearBirth', 'integer', array("label" => "", "required" => false))
			->add('dayDeath', 'integer', array("label" => "Date de décès", "required" => false))
			->add('monthDeath', 'integer', array("label" => "", "required" => false))
			->add('yearDeath', 'integer', array("label" => "", "required" => false))
			->add('country', 'choice', array(
											'label' => 'Pays', 
											'multiple' => false, 
											'expanded' => false,
											'constraints' => array(new Assert\NotBlank()),
											'empty_value' => 'Choisissez une option',
										    'choices' => $countryArray
											))	
            ->add('save', 'submit', array('label' => 'Sauvegarder', 'attr' => array('class' => 'btn btn-success')))
			;
    }

    public function getName()
    {
        return 'biography';
    }
}
