<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.page.Title"
            ))
			->add('text', TextareaType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.page.Text", 'attr' => array('class' => 'redactor')
            ))
			->add('internationalName', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.page.InternationalName"
            ))
			->add('photo', FileType::class, array('data_class' => null, "label" => "admin.page.Image", "required" => true))
            ->add('save', SubmitType::class, array('label' => 'admin.main.Save', 'attr' => array('class' => 'btn btn-success')))
			;
    }
	
    public function getName()
    {
        return 'page';
    }
}