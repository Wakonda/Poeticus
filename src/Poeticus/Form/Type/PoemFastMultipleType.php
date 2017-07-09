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

class PoemFastMultipleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$collectionArray = $options['collections'];
		$poeticFormArray = $options["poeticForms"];
		$languageArray = $options["languages"];
		$locale = $options["locale"];

        $builder
			->add('ipProxy', TextType::class, array(
                'label' => 'Adresse Proxy', 'required' => false, 'mapped' => false, 'constraints' => [new Assert\Regex("#^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}:[0-9]{2,4}$#")]
            ))

			->add('url', TextType::class, array(
                'constraints' => [new Assert\NotBlank(), new Assert\Url()], 'label' => 'URL', 'mapped' => false
            ))

			->add('releasedDate', IntegerType::class, array(
                'label' => 'Date de publication'
            ))
			
			->add('unknownReleasedDate', CheckboxType::class, array(
                'mapped' => false, 'label' => 'Date inconnue'
            ))
            ->add('biography', TextType::class, array(
                'label' => 'Biographie',
				'constraints' => new Assert\NotBlank()
            ))
			->add('collection', ChoiceType::class, array(
				'label' => 'Recueil', 
				'multiple' => false,
				'required' => false,
				'expanded' => false,
				'placeholder' => 'main.field.ChooseAnOption',
				'choices' => $collectionArray
			))
			->add('number', IntegerType::class, array(
				'label' => 'Nombre',
				'required' => true,
				'mapped' => false
			))
            ->add('poeticform', ChoiceType::class, array(
				'label' => 'admin.poem.PoeticForm', 
				'multiple' => false,
				'required' => false,
				'expanded' => false,
				'placeholder' => 'main.field.ChooseAnOption',
				'choices' => $poeticFormArray
			))
			->add('language', ChoiceType::class, array(
				'label' => 'admin.form.Language', 
				'multiple' => false,
				'required' => true,
				'expanded' => false,
				'placeholder' => 'main.field.ChooseAnOption',
				'choices' => $languageArray,
				'data' => $locale,
				'constraints' => new Assert\NotBlank()
			))
            ->add('save', SubmitType::class, array('label' => 'Ajouter', 'attr' => array('class' => 'btn btn-success')));
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"poeticForms" => null,
			"collections" => null,
			"languages" => null,
			"locale" => null
		));
	}

    public function getName()
    {
        return 'poemfastmultiple';
    }
}