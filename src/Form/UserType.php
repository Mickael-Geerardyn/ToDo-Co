<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
	{
        $builder
            ->add('username', TextType::class, [
				'label' => "Nom d'utilisateur",
				'attr' => ['class' => 'form-control']
			])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent correspondre.',
				'required' => $options['is_create'],
				'mapped' => $options['is_create'],
                'first_options'  => [
					'label' => 'Mot de passe',
					'attr' => ['class' => 'form-control']
					],
                'second_options' => [
					'label' => 'Tapez le mot de passe à nouveau',
					'attr' => ['class' => 'form-control']
				],
            ])
            ->add('email', EmailType::class, [
				'label' => 'Adresse email',
				'attr' => ['class' => 'form-control']
			])
			->add('roles', ChoiceType::class, [
				'choices'  => [
					'Administrateur' => 'ROLE_ADMIN',
					'Utilisateur'  => 'ROLE_USER'
				],
				'expanded' => true,
				'mapped' => false,
				'required' => true
				])
        ;
    }

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => User::class,
			"is_create" => false
							   ]);
	}
}
