<?php

namespace Doinc\PersonaKyc\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use ReflectionClass;

class UpdateCommand extends Command
{
    public $signature = 'persona:update';

    public $description = 'Update the generated stubs';

    public function handle(): int
    {
        $this->info("Updating PersonaTemplates");
        $this->info("Compiling ...");
        // retrieve the current working directory and create the path to the PersonaTemplates stub and the final folder
        $current_path = $this->getPackageBaseDir();
        $persona_template_path = $current_path . "/../stubs/PersonaTemplates.php.stub";
        $persona_final_template_path = $this->getLaravel()->basePath("app/Enums");

        // read the stub and remove its closing tag (and newline)
        $content = file_get_contents($persona_template_path);
        $content = Str::replaceLast("}\n", "", $content);

        // start looping on the templates array defined in the config, for each of the record in the array a new
        // record is added to the enum
        $templates = config("persona-kyc.templates");
        foreach ($templates as $key => $value) {
            $content .= "\tcase {$key} = \"{$value}\";\n";
        }
        // close the enum
        $content .= "}\n";

        // check if the enum folder exists, if it does not than create it
        if(!file_exists($persona_final_template_path)) {
            mkdir($persona_final_template_path);
        }
        // finally write the file to the enum folder
        file_put_contents($persona_final_template_path . "/PersonaTemplates.php", $content);
        $this->info("PersonaTemplates compiled successfully!");
        $this->info("Update completed!");
        return self::SUCCESS;
    }

    protected function getPackageBaseDir(): string
    {
        $reflector = new ReflectionClass(get_class($this));

        return dirname($reflector->getFileName());
    }
}
