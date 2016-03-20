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
		$biographyArray = $options["biographies"];
		$countryArray = $options["countries"];
		$collectionArray = $options["collections"];

        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), 'label' => 'Titre'
            ))
            ->add('poeticform', ChoiceType::class, array(
											'label' => 'Forme poétique', 
											'multiple' => false,
											'required' => false,
											'expanded' => false,
											'placeholder' => 'Choisissez une option',
											'choices' => $poeticFormArray
											))
			->add('text', TextareaType::class, array(
                'attr' => array('class' => 'redactor'), 'label' => 'Texte'
            ))
			->add('releasedDate', IntegerType::class, array(
                'label' => 'Date de publication'
            ))
			
			->add('unknownReleasedDate', CheckboxType::class, array(
                'mapped' => false, 'label' => 'Date inconnue'
            ))
			
            ->add('authorType', ChoiceType::class, array(
											'label' => 'Type d\'auteur', 
											'multiple' => false, 
											'expanded' => false,
											'constraints' => array(new Assert\NotBlank()),
										    'choices' => array("Biographie" => "biography", "Utilisateur" => "user"),
											'attr' => array('class' => 'authorType_select')
											))
			->add('user', ChoiceType::class, array(
											'label' => 'Utilisateur', 
											'multiple' => false, 
											'expanded' => false,
											'placeholder' => 'Choisissez une option',
										    'choices' => $userArray
											))
            ->add('biography', TextType::class, array(
                'label' => 'Biographie'
            ))
			/*->add('biography', ChoiceType::class, array(
											'label' => 'Biographie', 
											'multiple' => false, 
											'expanded' => false,
											'placeholder' => 'Choisissez une option',
										    'choices' => $biographyArray
											))*/
											
			->add('country', ChoiceType::class, array(
											'label' => 'Pays', 
											'multiple' => false, 
											'expanded' => false,
											'constraints' => array(new Assert\NotBlank()),
											'placeholder' => 'Choisissez une option',
										    'choices' => $countryArray
											))		
			->add('collection', ChoiceType::class, array(
											'label' => 'Recueil', 
											'multiple' => false,
											'required' => false,
											'expanded' => false,
											'placeholder' => 'Choisissez une option',
										    'choices' => $collectionArray
											))
			->add('photo', FileType::class, array('data_class' => null, "label" => "Image", "required" => true
            ))
            ->add('save', SubmitType::class, array('label' => 'Sauvegarder', 'attr' => array('class' => 'btn btn-success')));
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"biographies" => null,
			"countries" => null,
			"collections" => null,
			"poeticForms" => null,
			"users" => null
		));
	}
	
    public function getName()
    {
        return 'poem';
    }
}