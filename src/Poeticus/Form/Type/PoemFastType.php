<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class PoemFastType extends AbstractType
{
	private $biographyArray;
	private $countryArray;
	private $collectionArray;

	public function __construct($biographyArray, $countryArray, $collectionArray)
	{
		$this->biographyArray = $biographyArray;
		$this->countryArray = $countryArray;
		$this->collectionArray = $collectionArray;
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$biographyArray = $this->biographyArray;
		$countryArray = $this->countryArray;
		$collectionArray = $this->collectionArray;

        $builder
			->add('url', 'text', array(
                'constraints' => new Assert\NotBlank(), 'label' => 'URL', 'mapped' => false
            ))

			->add('releasedDate', 'integer', array(
                'label' => 'Date de publication'
            ))
			
			->add('unknownReleasedDate', 'checkbox', array(
                'mapped' => false, 'label' => 'Date inconnue'
            ))
			->add('biography', 'choice', array(
											'label' => 'Biographie', 
											'multiple' => false, 
											'expanded' => false,
											'empty_value' => 'Choisissez une option',
											'constraints' => array(new Assert\NotBlank()),
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
			
            ->add('save', 'submit', array('label' => 'Ajouter'));
    }

    public function getName()
    {
        return 'poemfast';
    }
}
