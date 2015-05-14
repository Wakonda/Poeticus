<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CountryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'constraints' => new Assert\NotBlank(), "label" => "Titre"
            ))
			->add('internationalName', 'text', array(
                'constraints' => new Assert\NotBlank(), "label" => "Nom international", 'attr' => array('class' => 'redactor')
            ))
			->add('flag', 'file', array('data_class' => null, "label" => "Drapeau", "required" => false
            ))
            ->add('save', 'submit', array('label' => 'Sauvegarder'))
			;
    }

    public function getName()
    {
        return 'country';
    }
}
