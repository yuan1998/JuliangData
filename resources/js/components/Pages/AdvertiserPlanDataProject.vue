<template>
    <div class="page-advertiser-plan-data-project" v-loading="loading">
        <el-card>
            <div class="filter">
                <div class="filter-title">
                    条件筛选
                </div>
                <el-form :inline="true" :model="form" size="mini" :rules="rules" ref="form">
                    <el-row :gutter="15">
                        <el-col :span="12">
                            <el-form-item label="日期范围" prop="dates">
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
                        </el-col>
                    </el-row>
                    <div>
                        <el-button size="mini" type="primary" @click="onSubmit">
                            <i class="el-icon-search"></i>
                            查询
                        </el-button>
                        <el-button size="mini" @click="handleResetForm">
                            <i class="el-icon-refresh"></i>
                            重置
                        </el-button>
                    </div>
                </el-form>
            </div>
            <div class="table-content">
                <template v-if="data">
                    <el-table
                            :data="projectData"
                            border
                            style="width: 100%">
                        <el-table-column
                                prop="comment_name"
                                fixed
                                label="账户名称"
                                width="280">
                        </el-table-column>
                        <el-table-column
                                prop="show"
                                label="展现数"
                                width="180">
                        </el-table-column>
                        <el-table-column
                                prop="click"
                                label="点击数	">
                        </el-table-column>
                        <el-table-column
                                prop="cost"
                                label="消耗(虚)	">
                        </el-table-column>
                        <el-table-column
                                prop="cost_off"
                                label="消耗(实)	">
                        </el-table-column>
                        <el-table-column
                                prop="ctr"
                                label="点击率	">
                        </el-table-column>
                        <el-table-column
                                prop="avg_click_cost"
                                label="平均点击单价	">
                        </el-table-column>
                        <el-table-column
                                prop="avg_show_cost"
                                label="平均千次展现费用	">
                        </el-table-column>
                        <el-table-column
                                prop="attribution_convert"
                                label="转化数	">
                        </el-table-column>
                        <el-table-column
                                prop="attribution_convert_cost"
                                label="转化成本	">
                        </el-table-column>

                    </el-table>

                </template>
            </div>
        </el-card>

    </div>
</template>

<script>

    const today = new Date();

    export default {
        name    : "advertiser-plan-data-project",
        data() {
            return {
                loading      : false,
                data         : null,
                isError      : false,
                projectList  : [],
                form         : {
                    dates: [ today, today ],
                },
                rules        : {
                    dates: [
                        { type: 'array', required: true, message: '请选择时间', trigger: 'change' }
                    ]
                },
                pickerOptions: {
                    shortcuts: [ {
                        text: '今天',
                        onClick(picker) {
                            picker.$emit('pick', [ today, today ]);
                        }
                    }, {
                        text: '昨天',
                        onClick(picker) {
                            picker.$emit('pick', [ start, start ]);
                        }
                    }, {
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

            };
        },
        computed: {
            projectData() {
                if (!this.data) return [];


            }
        },
        mounted() {
            this.handlePost();
        },
        methods : {
            handleResetForm() {
                this.$refs[ 'form' ].resetFields();
            },
            async onSubmit() {
                this.$refs[ 'form' ].validate((valid) => {
                    if (valid) {
                        this.handlePost();
                    }
                })

            },
            async handlePost() {
                try {
                    this.isError = false;
                    this.loading = true;
                    let result   = await axios.post('/admin/jl-advertiser-plan-datas/plan/list', this.form);
                    this.loading = false;
                    this.data    = result.data;

                } catch (e) {
                    this.loading = false;
                    this.isError = true;
                }
            },
        },
    }
</script>

<style scoped lang="less">

</style>
