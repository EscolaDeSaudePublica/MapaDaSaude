<?php

namespace RegistrationFieldTypes;

use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space;
use MapasCulturais\Definitions\RegistrationFieldType;

class Module extends \MapasCulturais\Module
{
    public function _init()
    {
    }

    public function register()
    {
        $app = App::i();
        foreach ($this->getRegistrationFieldTypesDefinitions() as $definition) {
            $app->registerRegistrationFieldType(new RegistrationFieldType($definition));
        }

        $agent_fields = ['name', '_type', 'shortDescription', '@location'];
        
        $definitions = Agent::getMetadataMetadata();
        
        foreach ($definitions as $key => $def) {
            $def = (object) $def;
            if ($def->isMetadata && $def->available_for_opportunities) {
                $agent_fields[] = $key;
            }
        }
        
        $this->_config['availableAgentFields'] = $agent_fields;
        //Campos do espaço disponiveis na oportunidade
        $space_fields = ['name', '_type', 'shortDescription', '@location'];
        
        $definitions = Space::getMetadataMetadata();
        
        foreach ($definitions as $key => $def) {
            $def = (object) $def;
            if ($def->isMetadata && $def->available_for_opportunities) {
                $agent_fields[] = $key;
            }
        }
        
        $this->_config['availableSpaceFields'] = $agent_fields;
        
    }

    function getRegistrationFieldTypesDefinitions()
    {
        $registration_field_types = [
            [
                'slug' => 'textarea',
                'name' => \MapasCulturais\i::__('Campo de texto (textarea)'),
                'viewTemplate' => 'registration-field-types/textarea',
                'configTemplate' => 'registration-field-types/textarea-config',
            ],
            [
                'slug' => 'text',
                'name' => \MapasCulturais\i::__('Campo de texto simples'),
                'viewTemplate' => 'registration-field-types/text',
                'configTemplate' => 'registration-field-types/text-config',
            ],
            [
                'slug' => 'date',
                'name' => \MapasCulturais\i::__('Campo de data'),
                'viewTemplate' => 'registration-field-types/date',
                'configTemplate' => 'registration-field-types/date-config',
            ],
            [
                'slug' => 'url',
                'name' => \MapasCulturais\i::__('Campo de URL (link)'),
                'viewTemplate' => 'registration-field-types/url',
                'configTemplate' => 'registration-field-types/url-config',
                'validations' => [
                    'v::url()' => \MapasCulturais\i::__('O valor não é uma URL válida')
                ]
            ],
            [
                'slug' => 'email',
                'name' => \MapasCulturais\i::__('Campo de email'),
                'viewTemplate' => 'registration-field-types/email',
                'configTemplate' => 'registration-field-types/email-config',
                'validations' => [
                    'v::email()' => \MapasCulturais\i::__('O valor não é um endereço de email válido')
                ]
            ],
            [
                'slug' => 'select',
                'name' => \MapasCulturais\i::__('Seleção única (select)'),
                'viewTemplate' => 'registration-field-types/select',
                'configTemplate' => 'registration-field-types/select-config',
                'requireValuesConfiguration' => true
            ],
            [
                'slug' => 'section',
                'name' => \MapasCulturais\i::__('Título de Seção'),
                'viewTemplate' => 'registration-field-types/section',
                'configTemplate' => 'registration-field-types/section-config',
            ],
            [
                'slug' => 'number',
                'name' => \MapasCulturais\i::__('Campo numérico'),
                'viewTemplate' => 'registration-field-types/number',
                'configTemplate' => 'registration-field-types/number-config',
                'validations' => [
                    'v::numeric()' => \MapasCulturais\i::__('O valor inserido não é válido')
                ]
            ],
            [
                'slug' => 'cpf',
                'name' => \MapasCulturais\i::__('Campo de CPF'),
                'viewTemplate' => 'registration-field-types/cpf',
                'configTemplate' => 'registration-field-types/cpf-config',
                'validations' => [
                    'v::cpf()' => \MapasCulturais\i::__('O cpf inserido não é válido')
                ]
            ],
            [
                'slug' => 'cnpj',
                'name' => \MapasCulturais\i::__('Campo de CNPJ'),
                'viewTemplate' => 'registration-field-types/cnpj',
                'configTemplate' => 'registration-field-types/cnpj-config',
                'validations' => [
                    'v::cnpj()' => \MapasCulturais\i::__('O cnpj inserido não é válido')
                ]
            ],
            [
                'slug' => 'checkboxes',
                'name' => \MapasCulturais\i::__('Seleção múltipla (checkboxes)'),
                'viewTemplate' => 'registration-field-types/checkboxes',
                'configTemplate' => 'registration-field-types/checkboxes-config',
                'requireValuesConfiguration' => true,
                'serialize' => function ($value) {
                    if (!is_array($value)) {
                        if ($value) {
                            $value = [$value];
                        } else {
                            $value = [];
                        }
                    }
                    return json_encode($value);
                },
                'unserialize' => function ($value) {
                    return json_decode($value);
                }
            ],
            [
                'slug' => 'agent-owner-field',
                // o espaço antes da palavra Campo é para que este tipo de campo seja o primeiro da lista
                'name' => \MapasCulturais\i::__(' Campo do Agente Responsável'),
                'viewTemplate' => 'registration-field-types/agent-owner-field',
                'configTemplate' => 'registration-field-types/agent-owner-field-config',
                'requireValuesConfiguration' => true,
                'serialize' => function ($value, $registration = null, $metadata_definition = null) {
                    if(isset($metadata_definition->config['registrationFieldConfiguration']->config['agentField'])){
                        $agent_field = $metadata_definition->config['registrationFieldConfiguration']->config['agentField'];
                        $agent = $registration->owner;

                        $agent->$agent_field = $value;
                        $agent->save();
                    }

                    return json_encode($value);
                },
                'unserialize' => function ($value, $registration = null, $metadata_definition = null) {
                    if(isset($metadata_definition->config['registrationFieldConfiguration']->config['agentField'])){
                        $agent_field = $metadata_definition->config['registrationFieldConfiguration']->config['agentField'];
                        $agent = $registration->owner;
                        return $agent->$agent_field;
                    } else {
                        return json_decode($value);
                    }
                }
            ],
            [
                'slug' => 'space-field',
                'name' => \MapasCulturais\i::__(' Campo do Espaço'),
                'viewTemplate' => 'registration-field-types/space-field',
                'configTemplate' => 'registration-field-types/space-field-config',
                'requireValuesConfiguration' => true,
                'serialize' => function ($value) {
                    return json_encode($value);
                },
                'unserialize' => function ($value) {
                    return json_decode($value);
                }
            ]

        ];

        return $registration_field_types;
    }
}
