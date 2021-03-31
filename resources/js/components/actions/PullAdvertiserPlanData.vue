<template>
    <div style="display: none;" :style="'display:inline-block;'">
        <el-button size="mini" type="primary" @click="handleOpenModel">
            导入数据
        </el-button>
        <el-dialog
                title="手动导入广告计划数据"
                :visible.sync="dialogVisible"
                :before-close="handleClose"
                width="50%">
            <div v-loading="posting">
                <p>
                    <i class="el-icon-warning-outline"></i>
                    每天会自动拉取前一天的数据,如果觉得数据不对,可以使用该操作手动拉取广告计划数据.123
                </p>
                <el-form ref="form" :rules="rules" :model="form" label-width="80px">
                    <el-form-item label="医院类型" prop="hospital_id">
                        <el-radio-group v-model="form.hospital_id" size="medium">
                            <el-radio v-for="(item, key) in hospitalTypeList" :key="key" border :label="key">
                                {{ item }}
                            </el-radio>
                        </el-radio-group>
                    </el-form-item>
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
                </el-form>
                <div class="dialog-footer">
                    <el-button @click="dialogVisible = false">取 消</el-button>
                    <el-button type="primary" @click="handlePullData">确 定</el-button>
                </div>
            </div>
            <div>
                <el-progress :percentage="percentage"></el-progress>
                <div>
                    <div v-for="(item,index) in resultLogs" :key="index">
                        {{ item.date }} : {{ item.result }}
                    </div>
                </div>
            </div>


        </el-dialog>
    </div>
</template>

<script>
    import { datesRange } from "../../Utils/common";

    export default {
        name    : "action-pull-advertiser-plan-data",
        props   : {
            hospitalTypeList: Object,
        },
        mounted() {
            console.log('this.hospitalTypeList :', this.hospitalTypeList);
        },
        data() {
            return {

                dialogVisible: false,
                value2       : '',
                form         : {
                    dates      : '',
                    hospital_id: ''
                },
                rules        : {
                    dates      : [
                        { required: true, message: '请选择时间', trigger: 'change' }
                    ],
                    hospital_id: [
                        { required: true, message: '请选择医院类型', trigger: 'change' }
                    ],

                },
                pickerOptions: {
                    shortcuts: [
                        {
                            text: '昨天!!!',
                            onClick(picker) {
                                const date = new Date();
                                date.setTime(date.getTime() - 3600 * 1000 * 24);
                                picker.$emit('pick', [ date, date ]);
                            }
                        },
                        {
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
                count        : 0,
                completeCount: 0,
                resultLogs   : [],
            };
        },
        computed: {
            percentage() {
                if (!this.count) {
                    console.log('this.count :', this.count);
                    return 0;
                }
                return Math.ceil((this.completeCount / this.count) * 100);
            }
        },
        methods : {
            handleClose(done) {
                if (this.posting)
                    return;
                done();

            },
            async pullAll() {
                let result = datesRange(this.form.dates[ 0 ], this.form.dates[ 1 ])
                    .map(async (date) => {
                        console.log('date :', date);
                        this.count++;
                        let res = await axios.get('/api/v1/juliang/advertiser_plan_data_pull', {
                            params: {
                                ...this.form,
                                dates: [
                                    date,
                                    date
                                ]
                            },
                        });
                        this.completeCount++;
                        return {
                            date,
                            result: res?.data?.code === 0
                        };
                    });

                return await Promise.all(result);
            },
            async mapToPullApi() {
                this.count         = 0;
                this.completeCount = 0;
                this.resultLogs    = [];
                this.posting       = true;

                this.resultLogs = await this.pullAll();

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
