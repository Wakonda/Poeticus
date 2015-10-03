<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class PoemType extends AbstractType
{
	private $poeticFormArray;
	private $userArray;
	private $biographyArray;
	private $countryArray;
	private $collectionArray;

	public function __construct($poeticFormArray, $userArray, $biographyArray, $countryArray, $collectionArray)
	{
		$this->poeticFormArray = $poeticFormArray;
		$this->userArray = $userArray;
		$this->biographyArray = $biographyArray;
		$this->countryArray = $countryArray;
		$this->collectionArray = $collectionArray;
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$poeticFormArray = $this->poeticFormArray;
		$userArray = $this->userArray;
		$biographyArray = $this->biographyArray;
		$countryArray = $this->countryArray;
		$collectionArray = $this->collectionArray;

        $builder
            ->add('title', 'text', array(
                'constraints' => new Assert\NotBlank(), 'label' => 'Titre'
            ))
			->add('text', 'textarea', array(
                'constraints' => new Assert\NotBlank(), 'attr' => array('class' => 'redactor'), 'label' => 'Texte'
            ))
			->add('releasedDate', 'integer', array(
                'label' => 'Date de publication'
            ))
			
			->add('unknownReleasedDate', 'checkbox', array(
                'mapped' => false, 'label' => 'Date inconnue'
            ))
			
            ->add('authorType', 'choice', array(
											'label' => 'Type d\'auteur', 
											'multiple' => false, 
											'expanded' => false,
											'constraints' => array(new Assert\NotBlank()),
										    'choices' => array("biography" => "Biographie", "user" => "Utilisateur"),
											'attr' => array('class' => 'authorType_select')
											))
            ->add('poeticform', 'choice', array(
											'label' => 'Forme poétique', 
											'multiple' => false,
											'required' => false,
											'expanded' => false,
											'empty_value' => 'Choisissez une option',
											'choices' => $poeticFormArray
											))
			->add('user', 'choice', array(
											'label' => 'Utilisateur', 
											'multiple' => false, 
											'expanded' => false,
											'empty_value' => 'Choisissez une option',
										    'choices' => $userArray
											))

			->add('biography', 'choice', array(
											'label' => 'Biographie', 
											'multiple' => false, 
											'expanded' => false,
											'empty_value' => 'Choisissez une option',
										    'choices' => $biographyArray
											))
											
			->add('country', 'choice', array(
											'label' => 'Pays', 
											'multiple' => false, 
											'expanded' => false,
											'constraints' => array(new Assert\NotBlank()),
											'empty_value' => 'Choisissez une option',
										    'choices' => $countryArray
											))		
			->add('collection', 'choice', array(
											'label' => 'Recueil', 
											'multiple' => false,
											'required' => false,
											'expanded' => false,
											'empty_value' => 'Choisissez une option',
										    'choices' => $collectionArray
											))
			
            ->add('save', 'submit', array('label' => 'Sauvegarder'));
    }

    public function getName()
    {
        return 'poem';
    }
}
