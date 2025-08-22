<template>
    <div>
        <ui-input
            :value="value"
            disabled
        />
    </div>
</template>

<script>
import { FieldtypeMixin as Fieldtype } from '@statamic/cms';

export default {
    mixins: [Fieldtype],
};
</script>
