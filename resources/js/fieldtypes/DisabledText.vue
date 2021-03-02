<template>
    <div>
        <input type="text" :value="value" class="input-text bg-grey-30" disabled />
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],
};
</script>
