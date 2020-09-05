<template>
    <el-button size="mini" @click="handleClick">生成授权链接</el-button>
</template>

<script>
    export default {
        name   : "",
        props  : {
            data: Object,
        },
        mounted() {
            console.log('this.data :');
        },
        data() {
            return {};
        },
        methods: {
            handleClick() {
                this.$bus.$emit('test', this.data);
            }
        },
    }
</script>

<style scoped lang="less">

</style>
