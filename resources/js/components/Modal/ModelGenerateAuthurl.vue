<template>
    <div>
        <el-dialog
                :title="itemData ? itemData.name : '生成授权URL'"
                :visible.sync="dialogVisible"
                width="50%"
                :before-close="handleCloseBefore">
            <div v-if="itemData">

                <el-form ref="form" :rules="rules" :model="form" label-width="80px">
                    <el-form-item label="APP ID">
                        <p>
                            {{ itemData.app_id }}
                        </p>
                    </el-form-item>
                    <el-form-item label="APP Secret">
                        <p>
                            {{ itemData.app_secret }}
                        </p>
                    </el-form-item>
                    <el-form-item label="医院类型" prop="hospital_id">
                        <el-radio-group v-model="form.hospital_id" size="medium">
                            <el-radio v-for="(value, key) in hospitalTypeList" :key="key" border :label="key">
                                {{ value }}
                            </el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="授权链接" v-if="url">
                        <p>
                            {{ url }}
                            <el-button @click="copyUrl" icon="el-icon-document-copy" type="text"></el-button>
                        </p>
                    </el-form-item>
                </el-form>
            </div>
            <div slot="footer" class="dialog-footer">
                <el-button @click="handleClose">取 消</el-button>
                <el-button type="primary" @click="handleSubmit">生 成</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    export default {
        name   : "modal-generate-auth-url",
        props  : {
            hospitalTypeList: Object,
        },
        data() {
            return {
                itemData     : null,
                dialogVisible: false,
                url          : '',
                form         : {
                    hospital_id: '',
                },
                rules        : {
                    hospital_id: [
                        { required: true, message: '请选择医院类型', trigger: 'change' }
                    ],

                },
            };
        },
        mounted() {
            this.$bus.$on('test', (item) => {
                this.$set(this, 'itemData', item);
                console.log('item :', item);
            })
        },
        watch  : {
            itemData(val) {
                this.dialogVisible = !!val;
            }
        },
        methods: {
            // https://ad.oceanengine.com/openapi/audit/oauth.html?app_id=1668736156326939&state={%22hospital_id%22:%22zx%22,%22account_type%22:%22xian%22}&redirect_uri=http://juliang.xahmyk.cn/api/v1/juliang/auth_code/
            makeUrl() {
                let data = {
                    hospital_id: this.form.hospital_id,
                    app_id     : this.itemData.id,
                };

                let dataJson = JSON.stringify(data);

                return `https://ad.oceanengine.com/openapi/audit/oauth.html?app_id=${ this.itemData.app_id }&redirect_uri=http://juliang.xahmyk.cn/api/v1/juliang/auth_code/&state=${ dataJson }`

            },
            copyUrl() {
                this.$copyText(this.url).then(() => {
                    this.$notify({
                        title  : '成功',
                        message: '复制成功',
                        type   : 'success'
                    });
                }).catch(() => {
                    this.$notify({
                        title  : '失败',
                        message: '复制失败',
                        type   : 'error'
                    });
                })
            },
            handleSubmit() {
                this.$refs[ 'form' ].validate((valid) => {
                    if (!valid) return;

                    this.url = this.makeUrl();
                })
            },
            handleCloseBefore(done) {
                this.$confirm('确认关闭？')
                    .then(_ => {
                        done();
                        this.handleClose();
                    })
                    .catch(_ => {
                    });
            },
            handleClose() {
                this.$refs[ 'form' ].resetFields();
                this.url = '';
                this.$set(this, 'itemData', null);
            }
        },
    }
</script>

<style scoped lang="less">

</style>
