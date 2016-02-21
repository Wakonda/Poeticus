<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PoemFastType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$biographyArray = $options['biographies'];
		$countryArray = $options['countries'];
		$collectionArray = $options['collections'];

        $builder
			->add('url', TextType::class, array(
                'constraints' => new Assert\NotBlank(), 'label' => 'URL', 'mapped' => false
            ))

			->add('releasedDate', IntegerType::class, array(
                'label' => 'Date de publication'
            ))
			
			->add('unknownReleasedDate', CheckboxType::class, array(
                'mapped' => false, 'label' => 'Date inconnue'
            ))
            ->add('biography', TextType::class, array(
                'label' => 'Biographie'
            ))
			/*->add('biography', ChoiceType::class, array(
											'label' => 'Biographie', 
											'multiple' => false, 
											'expanded' => false,
											'placeholder' => 'Choisissez une option',
											'constraints' => array(new Assert\NotBlank()),
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
			
            ->add('save', SubmitType::class, array('label' => 'Ajouter', 'attr' => array('class' => 'btn btn-success')));
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"biographies" => null,
			"countries" => null,
			"collections" => null
		));
	}

    public function getName()
    {
        return 'poemfast';
    }
}