<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class CollectionType extends AbstractType
{
	private $biographyArray;

	public function __construct($biographyArray)
	{
		$this->biographyArray = $biographyArray;
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$biographyArray = $this->biographyArray;

        $builder
            ->add('title', 'text', array(
                'constraints' => new Assert\NotBlank(), "label" => "Titre"
            ))
			->add('text', 'textarea', array(
                'constraints' => new Assert\NotBlank(), "label" => "Texte", 'attr' => array('class' => 'redactor')
            ))
			->add('image', 'file', array('data_class' => null, "label" => "Image", "required" => false
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
										    'choices' => $biographyArray
											))
			
			->add('widgetProduct', 'textarea', array('required' => false, 'label' => 'Code produit'))
			
            ->add('save', 'submit', array('label' => 'Sauvegarder'))
			;
    }

    public function getName()
    {
        return 'collection';
    }
}
