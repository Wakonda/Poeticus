<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class IndexSearchType extends AbstractType
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
            ->add('title', 'text', array("label" => "Titre", "required" => false))
			->add('text', 'text', array("label" => "Mots-clés", "required" => false, "attr" => array("class" => "tagit full_width")))
			->add('author', 'text', array("label" => "Auteur", "required" => false))
			->add('country', 'choice', array(
											'label' => 'Pays', 
											'required' => false, 
											'empty_value' => 'Sélectionnez un pays', 
											'multiple' => false, 
											'expanded' => false,
											'constraints' => array(new Assert\NotBlank()),
										    'choices' => $countryArray))
			
			
			->add('collection', 'text', array("label" => "Recueil", "required" => false))
			->add('type', 'choice', array("label" => "Type", "choices" => array("biography" => "Grands auteurs", "user" => "Vos poésies"), "required" => false, "expanded" => false, "multiple" => false, "empty_value" => "Tous"))
            ->add('search', 'submit', array('label' => 'Rechercher', "attr" => array("class" => "btn btn-primary")))
			;
    }

    public function getName()
    {
        return 'index_search';
    }
}
