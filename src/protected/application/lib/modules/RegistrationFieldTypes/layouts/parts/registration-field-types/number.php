<div ng-class="field.error ? 'invalidField': '' " ng-if="::field.fieldType === 'number'" id="field_{{::field.id}}">
    <span class="label icon"> 
        {{::field.title}} 
        <span ng-if="requiredField(field)" class="required_form">(<?php \MapasCulturais\i::_e('Obrigatório'); ?>)</span>
    </span>
    
    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>
    
    <div><?php $this->part('registration-field-types/fields/number') ?></div>
</div>