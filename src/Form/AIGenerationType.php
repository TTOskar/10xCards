<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AIGenerationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('input_text', TextareaType::class, [
                'label' => 'Enter your text',
                'attr' => [
                    'rows' => 10,
                    'class' => 'block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                    'placeholder' => 'Paste your text here (minimum 1000 characters)...',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter some text',
                    ]),
                    new Length([
                        'min' => 1000,
                        'max' => 10000,
                        'minMessage' => 'Your text must be at least {{ limit }} characters long',
                        'maxMessage' => 'Your text cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Generate Flashcards',
                'attr' => [
                    'class' => 'text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => true,
        ]);
    }
} 