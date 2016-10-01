<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CountryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.country.Title"
            ))
			->add('internationalName', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.country.InternationalName", 'attr' => array('class' => 'redactor')
            ))
			->add('flag', FileType::class, array('data_class' => null, "label" => "admin.country.Flag", "required" => true
            ))
            ->add('save', SubmitType::class, array('label' => 'admin.main.Save', 'attr' => array('class' => 'btn btn-success')))
			;
    }

    public function getName()
    {
        return 'country';
    }
}