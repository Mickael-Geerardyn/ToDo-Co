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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, ['label' => "Nom d'utilisateur"])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent correspondre.',
                'required' => true,
				'mapped' => false,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Tapez le mot de passe à nouveau'],
				'constraints' => [
					new NotBlank([
									 'message' => 'Veuillez entrer un mot de passe valide',
								 ]),
					new Length([
								   'min' => 6,
								   'minMessage' => 'Votre mot de passe doit contenir au minimum {{ limit }} caractères',
								   // max length allowed by Symfony for security reasons
								   'max' => 4096,
							   ]),
				],
            ])
            ->add('email', EmailType::class, ['label' => 'Adresse email'])
			->add('roles', ChoiceType::class, [
				'choices'  => [
					'Utilisateur'  => 'ROLE_USER',
					'Administrateur' => 'ROLE_ADMIN'
				],
				'expanded' => true,
				'mapped' => false
				])
        ;
    }

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
								   'data_class' => User::class,
							   ]);
	}
}
