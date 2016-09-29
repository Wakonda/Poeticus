<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BiographyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$countryArray = $options["countries"];
		$languageArray = $options["languages"];

        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "Titre"
            ))
			->add('text', TextareaType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "Texte", 'attr' => array('class' => 'redactor')
            ))
			->add('photo', FileType::class, array('data_class' => null, "label" => "Photo", "required" => true))
			->add('dayBirth', IntegerType::class, array("label" => "Date de naissance", "required" => false))
			->add('monthBirth', IntegerType::class, array("label" => "", "required" => false))
			->add('yearBirth', IntegerType::class, array("label" => "", "required" => false))
			->add('dayDeath', IntegerType::class, array("label" => "Date de décès", "required" => false))
			->add('monthDeath', IntegerType::class, array("label" => "", "required" => false))
			->add('yearDeath', IntegerType::class, array("label" => "", "required" => false))
			->add('country', ChoiceType::class, array(
				'label' => 'Pays', 
				'multiple' => false, 
				'expanded' => false,
				'constraints' => array(new Assert\NotBlank()),
				'placeholder' => 'Choisissez une option',
				'choices' => $countryArray
			))
            ->add('save', SubmitType::class, array('label' => 'Sauvegarder', 'attr' => array('class' => 'btn btn-success')))
			;
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"countries" => null,
			"languages" => null
		));
	}
	
    public function getName()
    {
        return 'biography';
    }
}