<template>
    <div style="display: none;" :style="'display:inline-block;'">
        <el-button size="mini" type="primary" @click="handleOpenModel">
            导入数据
        </el-button>
        <el-dialog
                title="手动导入广告计划数据"
                :visible.sync="dialogVisible"
                width="50%">
            <p>
                <i class="el-icon-warning-outline"></i>
                每天会自动拉取前一天的数据,如果觉得数据不对,可以使用该操作手动拉取广告计划数据.
            </p>
            <el-form ref="form" :rules="rules" :model="form" label-width="80px">
                <el-form-item label="时间" prop="dates">
                    <el-date-picker
                            v-model="form.dates"
                            type="daterange"
                            align="right"
                            unlink-panels
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期"
                            :picker-options="pickerOptions">
                    </el-date-picker>
                </el-form-item>
                <el-form-item label="账户类型" prop="account_type">
                    <el-radio-group v-model="form.account_type" size="medium">
                        <el-radio v-for="(value, key) in accountTypeList" :key="key" border :label="key">
                            {{ value }}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>

            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogVisible = false">取 消</el-button>
                <el-button type="primary" @click="handlePullData">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    export default {
        name   : "action-pull-advertiser-plan-data",
        props  : {
            accountTypeList: Object,
        },
        data() {
            return {

                dialogVisible: false,
                value2       : '',
                form         : {
                    dates       : '',
                    account_type: '',
                },
                rules        : {
                    dates   : [
                        { type: 'date', required: true, message: '请选择时间', trigger: 'change' }
                    ],
                    resource: [
                        { required: true, message: '请选择账户类型', trigger: 'change' }
                    ],
                },
                pickerOptions: {
                    shortcuts: [ {
                        text: '最近一周',
                        onClick(picker) {
                            const end   = new Date();
                            const start = new Date();
                            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                            picker.$emit('pick', [ start, end ]);
                        }
                    }, {
                        text: '最近一个月',
                        onClick(picker) {
                            const end   = new Date();
                            const start = new Date();
                            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                            picker.$emit('pick', [ start, end ]);
                        }
                    }, {
                        text: '最近三个月',
                        onClick(picker) {
                            const end   = new Date();
                            const start = new Date();
                            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
                            picker.$emit('pick', [ start, end ]);
                        }
                    } ]
                },
                posting      : false,
            };
        },
        methods: {
            async mapToPullApi() {
                this.posting = true;
                this.showPostLoading();
                try {
                    let result = await axios.get('/api/v1/juliang/advertiser_plan_data_pull', {
                        params: this.form,
                    });
                    let data   = result.data;

                    if (data.code === 0) {
                        Swal.fire({
                            title  : '拉取数据成功!',
                            text   : '确认刷新页面!',
                            icon   : 'success',
                            onClose: () => {
                                this.dialogVisible = false;
                                $.admin.reload();
                            }
                        });
                    } else {
                        Swal.fire(
                            '错误!',
                            data.message,
                            'error',
                        );
                    }


                } catch (e) {
                    Swal.fire(
                        '错误!',
                        e.message,
                        'error',
                    );
                }
                this.posting = false;
            },
            showPostLoading() {
                swal.fire({
                    title            : '',
                    html             : `
                            <div class="save_loading">
                                <svg viewBox="0 0 140 140" width="140" height="140"><g class="outline"><path d="m 70 28 a 1 1 0 0 0 0 84 a 1 1 0 0 0 0 -84" stroke="rgba(0,0,0,0.1)" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></g><g class="circle"><path d="m 70 28 a 1 1 0 0 0 0 84 a 1 1 0 0 0 0 -84" stroke="#71BBFF" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-dashoffset="200" stroke-dasharray="300"></path></g></svg>
                            </div>
                            <div>
                                <h4>请稍等...</h4>
                            </div>
                            `,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
            },
            handlePullData() {
                this.$refs[ 'form' ].validate((valid) => {
                    if (valid) {
                        this.mapToPullApi();

                    }
                })
            },
            handleOpenModel() {
                this.dialogVisible = true;
            },

        },
    }
</script>

<style scoped lang="less">

</style>
