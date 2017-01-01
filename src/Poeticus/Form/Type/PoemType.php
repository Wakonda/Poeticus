<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PoemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$poeticFormArray = $options["poeticForms"];
		$userArray = $options["users"];
		$collectionArray = $options["collections"];
		$languageArray = $options["languages"];
		$locale = $options["locale"];

        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), 'label' => 'admin.poem.Title'
            ))
            ->add('poeticform', ChoiceType::class, array(
				'label' => 'admin.poem.PoeticForm', 
				'multiple' => false,
				'required' => false,
				'expanded' => false,
				'placeholder' => 'main.field.ChooseAnOption',
				'choices' => $poeticFormArray
			))
			->add('text', TextareaType::class, array(
                'attr' => array('class' => 'redactor'), 'label' => 'admin.poem.Text'
            ))
			->add('releasedDate', IntegerType::class, array(
                'label' => 'admin.poem.PublicationDate'
            ))
			
			->add('unknownReleasedDate', CheckboxType::class, array(
                'mapped' => false, 'label' => 'admin.poem.UnknownDate'
            ))
			
            ->add('authorType', ChoiceType::class, array(
				'label' => 'admin.poem.AuthorKind', 
				'multiple' => false, 
				'expanded' => false,
				'constraints' => array(new Assert\NotBlank()),
				'choices' => array("admin.poem.Biography" => "biography", "admin.poem.User" => "user"),
				'attr' => array('class' => 'authorType_select')
			))
			->add('user', ChoiceType::class, array(
				'label' => 'admin.poem.User', 
				'multiple' => false, 
				'expanded' => false,
				'placeholder' => 'main.field.ChooseAnOption',
				'choices' => $userArray
			))
            ->add('biography', TextType::class, array(
                'label' => 'admin.poem.Biography'
            ))
			->add('collection', ChoiceType::class, array(
				'label' => 'admin.poem.Collection', 
				'multiple' => false,
				'required' => false,
				'expanded' => false,
				'placeholder' => 'main.field.ChooseAnOption',
				'choices' => $collectionArray
			))
			->add('photo', FileType::class, array('data_class' => null, "label" => "Image", "required" => true))
			->add('language', ChoiceType::class, array(
				'label' => 'admin.form.Language', 
				'multiple' => false,
				'required' => false,
				'expanded' => false,
				'placeholder' => 'main.field.ChooseAnOption',
				'choices' => $languageArray,
				'data' => $locale
			))
            ->add('save', SubmitType::class, array('label' => 'admin.main.Save', 'attr' => array('class' => 'btn btn-success')));
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"collections" => null,
			"poeticForms" => null,
			"users" => null,
			"languages" => null,
			"locale" => null
		));
	}
	
    public function getName()
    {
        return 'poem';
    }
}