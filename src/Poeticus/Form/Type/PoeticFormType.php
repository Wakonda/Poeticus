<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class PoeticFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'constraints' => new Assert\NotBlank(), "label" => "Titre"
            ))
			->add('text', 'textarea', array(
                'constraints' => new Assert\NotBlank(), "label" => "Texte", 'attr' => array('class' => 'redactor')
            ))
			->add('image', 'file', array('data_class' => null, "label" => "Image", "required" => false
            ))
            ->add('save', 'submit', array('label' => 'Sauvegarder'))
			;
    }

    public function getName()
    {
        return 'poeticform';
    }
}
