<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$countryArray = $options['countries'];
		$ifEdit = $options['edit'];

        $builder
            ->add('username', TextType::class, array(
                'constraints' => new Assert\NotBlank(), 'label' => 'Pseudo'
            ))

            ->add('email', EmailType::class, array(
                'constraints' => new Assert\NotBlank(), 'label' => 'Email'
            ))

			->add('avatar', FileType::class, array(
                'data_class' => null, 'label' => 'Avatar', 'required' => false
            ))

			->add('gravatar', HiddenType::class, array(
                'label' => 'Avatar', 'required' => false
            ))
			
			->add('presentation', TextareaType::class, array(
                'constraints' => new Assert\NotBlank(), 'label' => 'Présentation'
            ))
			
			->add('country', ChoiceType::class, array(
											'label' => 'Pays', 
											'multiple' => false, 
											'expanded' => false,
											'constraints' => array(new Assert\NotBlank()),
											'placeholder' => 'Choisissez une option',
										    'choices' => $countryArray
											))
			
			
            ->add('save', SubmitType::class, array('label' => 'Sauvegarder', "attr" => array("class" => "btn btn-success")));
			
		if(!$this->ifEdit)
		{
			$builder
				->add('password', RepeatedType::class, array(
					'label' => 'Mot de passe',
					'type' => 'password',
					'invalid_message' => 'Les mots de passe doivent correspondre',
					'constraints' => new Assert\NotBlank(),
					'options' => array('required' => true),
					'first_options'  => array('label' => 'Mot de passe'),
					'second_options' => array('label' => 'Mot de passe (validation)'),
				))
				->add('captcha', TextType::class, array('label' => 'Recopiez le mot contenu dans l\'image', "mapped" => false, "attr" => array("class" => "captcha_word"), 'constraints' => new Assert\NotBlank()))
			;
		}
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"edit" => null,
			"countries" => null
		));
	}
	
    public function getName()
    {
        return 'user';
    }
}