<template>
    <div>
        <input type="text" :value="value" class="input-text" disabled />
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],
};
</script>
