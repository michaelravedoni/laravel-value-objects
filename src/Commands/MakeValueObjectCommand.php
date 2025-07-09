<?php

namespace MichaelRavedoni\LaravelValueObjects\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'make:value-object')]
class MakeValueObjectCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:value-object';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new value object class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Value Object';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../../stubs/value-object.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        // Par défaut, nous voulons que les objets valeurs soient créés dans app/ValueObjects
        return $rootNamespace . '\ValueObjects';
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() // Modifiez le type de retour en "int"
    {
        // Si le fichier existe déjà et que l'option --force n'est PAS utilisée
        if ($this->alreadyExists($this->getNameInput()) && ! $this->option('force')) {
            $this->error($this->type.' already exists!');

            // Retourne explicitement le code d'échec (1)
            return Command::FAILURE; // Utilisez Command::FAILURE ou 1
        }

        // Appelle la logique de génération du parent (création/écrasement)
        // La méthode handle() du parent est protégée, nous devons appeler la vraie logique
        // qui est implémentée dans la méthode fire() ou dans une logique interne de GeneratorCommand
        // Pour les versions récentes de Laravel, on utilise parent::handle() et on s'assure qu'il retourne bien un boolean.
        // Si GeneratorCommand::handle() retourne false en cas d'échec d'écriture, nous le propageons.

        // La méthode parent::handle() de GeneratorCommand gère déjà l'écriture et les messages.
        // Si le fichier est créé/écrasé avec succès, il renvoie void, ce qui est interprété comme 0.
        // Si une erreur d'écriture survient, il peut renvoyer false.
        $status = parent::handle();

        // Si parent::handle() n'a pas renvoyé false (donc succès ou void), on renvoie 0
        // Sinon, si parent::handle() a renvoyé false (échec d'écriture par ex), on renvoie 1
        return $status === false ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the value object already exists.'],
        ];
    }
}