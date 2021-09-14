<?php

namespace App\Form;

use App\Entity\Abonne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo')
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [ // il est préférable d'utiliser des contraintes que des controles côté front. L'utilisateur pourras activer le mode inspecter et mmodifier le code front pour contourner les controles 
                    new NotBlank([
                        'message' => 'Veuillez taper un mot de passe',
                    ]),
                    // new Length([
                    //     'min' => 6,
                    //     'minMessage' => 'Le mot de passe doit contenir au moin {{ limit }} caractères',
                    //     // max length allowed by Symfony for security reasons
                    //     'max' => 4096, // on peut modifier le nb maximale de caractères à notre guise
                    //     'maxMessage' => 'Le mot de passe dne doit pas dépasser les {{ limit }} caractères'
                    // ]),
                    new Regex([
                        "pattern" => "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[-+!*$@%_])([-+!*$@%_\w]{8,15})$/", // Regex101 : site pour tester les regex
                        "message" => "Le mot de passe doit être composé d'au moin une minisuclue, une majuscule, un chiffre, un caractère spéciale -+!*$@%_, et avoir entre 8 et 15 caractères"
                    ]) 
                ],
                'required' => false
            ])
            ->add("prenom", TextType::class, [
                "label" => "Prénom",
                "required" => false
            ])
            ->add("nom", TextType::class, [
                "required" => false
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false, // ça permet de dire que cette propriété ne fait pas partie de la classe abonne. Ceci permet d'éviter que ce soit modifié dans l'objet (voir ligne 22)
                'constraints' => [ // permet d'imposer des contraintes qui doivent être respecter par l'utilisateur
                    new IsTrue([
                        'message' => 'Vous devez accepter les C.G.U.',
                    ]),
                ],
                'label' => 'C.G.U.'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Abonne::class,
        ]);
    }
}
