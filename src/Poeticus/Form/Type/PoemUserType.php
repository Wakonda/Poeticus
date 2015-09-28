<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PoemUserType extends AbstractType
{
	public function __construct()
	{
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'constraints' => new Assert\NotBlank(), 'label' => 'Titre'
            ))
			->add('text', 'textarea', array(
                'constraints' => new Assert\NotBlank(), 'attr' => array('class' => 'redactor'), 'label' => 'Texte'
            ))
			
            ->add('save', 'submit', array('label' => 'Sauvegarder', 'attr' => array('class' => 'btn btn-success')))
            ->add('draft', 'submit', array('label' => 'Brouillon', 'attr' => array('class' => 'btn btn-primary')));
    }

    public function getName()
    {
        return 'poemuser';
    }
}
