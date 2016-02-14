<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PoeticFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "Titre"
            ))
			->add(TextType::class, 'textarea', array(
                'constraints' => new Assert\NotBlank(), "label" => "Texte", 'attr' => array('class' => 'redactor')
            ))
			->add('image', FileType::class, array('data_class' => null, "label" => "Image", "required" => true
            ))
            ->add('save', SubmitType::class, array('label' => 'Sauvegarder', 'attr' => array('class' => 'btn btn-success')))
			;
    }

    public function getName()
    {
        return 'poeticform';
    }
}
