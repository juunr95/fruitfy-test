<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->generateBrazilianName(),
            'email' => $this->generateEmail(),
            'phone' => $this->generateBrazilianPhone(),
        ];
    }

    /**
     * Generate a realistic Brazilian name
     */
    private function generateBrazilianName(): string
    {
        $firstNames = [
            'João', 'Maria', 'José', 'Ana', 'Francisco', 'Antônia', 'Carlos', 'Francisca',
            'Paulo', 'Antônio', 'Pedro', 'Luiza', 'Lucas', 'Marcos', 'Luiz', 'Adriana',
            'Rafael', 'Juliana', 'Marcelo', 'Mariana', 'Bruno', 'Fernanda', 'Eduardo', 'Patricia',
            'Roberto', 'Aline', 'Daniel', 'Sandra', 'Alexandre', 'Cristina', 'Rodrigo', 'Carla',
            'Fernando', 'Vanessa', 'Gustavo', 'Simone', 'Ricardo', 'Luciana', 'Felipe', 'Denise',
            'Thiago', 'Regina', 'Leandro', 'Michele', 'Diego', 'Camila', 'Vinicius', 'Leticia',
            'Leonardo', 'Renata', 'Matheus', 'Tatiane', 'Fabio', 'Priscila', 'Anderson', 'Amanda'
        ];

        $lastNames = [
            'Silva', 'Santos', 'Oliveira', 'Souza', 'Rodrigues', 'Ferreira', 'Alves', 'Pereira',
            'Lima', 'Gomes', 'Ribeiro', 'Carvalho', 'Barbosa', 'Araujo', 'Costa', 'Martins',
            'Lopes', 'Gonzaga', 'Fernandes', 'Vieira', 'Mendes', 'Cardoso', 'Rocha', 'Moreira',
            'Ramos', 'Nascimento', 'Castro', 'Correia', 'Teixeira', 'Dias', 'Fonseca', 'Moura',
            'Freitas', 'Monteiro', 'Machado', 'Cavalcanti', 'Nunes', 'Miranda', 'Pinto', 'Campos'
        ];

        $firstName = $this->faker->randomElement($firstNames);
        $lastName1 = $this->faker->randomElement($lastNames);
        $lastName2 = $this->faker->randomElement($lastNames);

        // Sometimes add middle name
        if ($this->faker->boolean(30)) {
            $middleName = $this->faker->randomElement($firstNames);
            return "{$firstName} {$middleName} {$lastName1} {$lastName2}";
        }

        return "{$firstName} {$lastName1} {$lastName2}";
    }

    /**
     * Generate a realistic email based on the name
     */
    private function generateEmail(): string
    {
        $domains = [
            'gmail.com', 'hotmail.com', 'yahoo.com.br', 'outlook.com', 'uol.com.br',
            'terra.com.br', 'bol.com.br', 'ig.com.br', 'live.com', 'globomail.com'
        ];

        $providers = [
            'empresa.com.br', 'trabalho.com', 'escritorio.com.br', 'consultoria.com',
            'tech.com.br', 'inovacao.com', 'solucoes.com.br', 'sistemas.com'
        ];

        $domain = $this->faker->boolean(70) 
            ? $this->faker->randomElement($domains)
            : $this->faker->randomElement($providers);

        // Generate username variations
        $baseUsername = $this->faker->randomElement([
            $this->faker->firstName(),
            $this->faker->firstName() . $this->faker->lastName(),
            $this->faker->firstName() . '.' . $this->faker->lastName(),
            $this->faker->firstName() . '_' . $this->faker->lastName(),
            $this->faker->firstName() . $this->faker->randomNumber(2),
        ]);

        $username = strtolower($this->removeAccents($baseUsername));
        
        return "{$username}@{$domain}";
    }

    /**
     * Generate a realistic Brazilian phone number
     */
    private function generateBrazilianPhone(): string
    {
        // Brazilian area codes
        $areaCodes = [
            '11', '12', '13', '14', '15', '16', '17', '18', '19', // São Paulo
            '21', '22', '24', // Rio de Janeiro
            '27', '28', // Espírito Santo
            '31', '32', '33', '34', '35', '37', '38', // Minas Gerais
            '41', '42', '43', '44', '45', '46', // Paraná
            '47', '48', '49', // Santa Catarina
            '51', '53', '54', '55', // Rio Grande do Sul
            '61', // Distrito Federal
            '62', '64', // Goiás
            '63', // Tocantins
            '65', '66', // Mato Grosso
            '67', // Mato Grosso do Sul
            '68', // Acre
            '69', // Rondônia
            '71', '73', '74', '75', '77', // Bahia
            '79', // Sergipe
            '81', '87', // Pernambuco
            '82', // Alagoas
            '83', // Paraíba
            '84', // Rio Grande do Norte
            '85', '88', // Ceará
            '86', '89', // Piauí
            '91', '93', '94', // Pará
            '92', '97', // Amazonas
            '95', // Roraima
            '96', // Amapá
            '98', '99', // Maranhão
        ];

        $areaCode = $this->faker->randomElement($areaCodes);
        
        // Mobile numbers start with 9
        if ($this->faker->boolean(80)) {
            $firstDigit = '9';
            $remainingDigits = $this->faker->numerify('####-####');
        } else {
            // Landline numbers
            $firstDigit = $this->faker->randomElement(['2', '3', '4', '5']);
            $remainingDigits = $this->faker->numerify('###-####');
        }

        return "({$areaCode}) {$firstDigit}{$remainingDigits}";
    }

    /**
     * Remove accents from strings
     */
    private function removeAccents(string $string): string
    {
        $unwanted_array = [
            'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
        ];
        
        return strtr($string, $unwanted_array);
    }

    /**
     * Create a contact with mobile phone
     */
    public function mobile(): static
    {
        return $this->state(function (array $attributes) {
            $areaCode = $this->faker->randomElement(['11', '21', '31', '41', '51', '61', '71', '81', '85']);
            $number = $this->faker->numerify('9####-####');
            
            return [
                'phone' => "({$areaCode}) {$number}",
            ];
        });
    }

    /**
     * Create a contact with landline phone
     */
    public function landline(): static
    {
        return $this->state(function (array $attributes) {
            $areaCode = $this->faker->randomElement(['11', '21', '31', '41', '51', '61', '71', '81', '85']);
            $firstDigit = $this->faker->randomElement(['2', '3', '4', '5']);
            $number = $this->faker->numerify('###-####');
            
            return [
                'phone' => "({$areaCode}) {$firstDigit}{$number}",
            ];
        });
    }
}
