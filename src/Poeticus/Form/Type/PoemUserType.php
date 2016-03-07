<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PoemUserType extends AbstractType
{
	public function __construct()
	{
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), 'label' => 'Titre'
            ))
			->add('text', TextareaType::class, array(
                'constraints' => new Assert\NotBlank(), 'attr' => array('class' => 'redactor'), 'label' => 'Texte'
            ))
			
            ->add('save', SubmitType::class, array('label' => 'Sauvegarder', 'attr' => array('class' => 'btn btn-success')))
            ->add('draft', SubmitType::class, array('label' => 'Brouillon', 'attr' => array('class' => 'btn btn-primary')));
    }

    public function getName()
    {
        return 'poemuser';
    }
}