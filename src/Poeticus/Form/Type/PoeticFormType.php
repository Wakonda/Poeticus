<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Poeticus\Entity\PoeticForm;

class PoeticFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.poeticForm.Title"
            ))
			->add('text', TextareaType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.poeticForm.Text", 'attr' => array('class' => 'redactor')
            ))
			->add('image', FileType::class, array('data_class' => null, "label" => "admin.poeticForm.Image", "required" => true
            ))
			->add('typeContentPoem', ChoiceType::class, array("label" => "admin.poeticForm.KindOfContent", "required" => true, "multiple" => false, "expanded" => false, 'choices' => ['admin.poeticForm.Image' => PoeticForm::IMAGETYPE, 'admin.poeticForm.Text' => PoeticForm::TEXTTYPE]
			))
            ->add('save', SubmitType::class, array('label' => 'admin.main.Save', 'attr' => array('class' => 'btn btn-success')))
			;
    }

    public function getName()
    {
        return 'poeticform';
    }
}