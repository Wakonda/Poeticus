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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$biographyArray = $options["biographies"];

        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "Titre"
            ))
			->add('text', Textarea::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "Texte", 'attr' => array('class' => 'redactor')
            ))
			->add('image', FileType:class, array('data_class' => null, "label" => "Image", "required" => true
            ))
			
			->add('releasedDate', IntegerType::class, array(
                'label' => 'Date de publication'
            ))
			
			->add('unknownReleasedDate', CheckboxType::class, array(
                'mapped' => false, 'label' => 'Date inconnue'
            ))
			
			/*->add('biography', ChoiceType::class, array(
											'label' => 'Biographie', 
											'multiple' => false, 
											'expanded' => false,
											'placeholder' => 'Choisissez une option',
										    'choices' => $biographyArray
											))*/
											
            ->add('biography', TextType::class, array(
                'label' => 'Biographie'
            ))
			
			->add('widgetProduct', Textarea::class, array('required' => false, 'label' => 'Code produit'))
			
            ->add('save', SubmitType::class, array('label' => 'Sauvegarder', 'attr' => array('class' => 'btn btn-success')))
			;
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"biographies" => null
		));
	}
	
    public function getName()
    {
        return 'collection';
    }
}