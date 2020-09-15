<template>
    <div class="page-account-plan-sum" v-loading="loading">
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
                        <el-col :span="12">
                            <el-form-item label="账户选择" prop="id">
                                <el-select style="max-width: 100%;" v-model="form.id" placeholder="账户选择" multiple
                                           collapse-tags>
                                    <el-option v-for="account in accounts"
                                               :key="account.id"
                                               :label="account.comment"
                                               :value="account.id">
                                        {{ account.advertiser_name }} ({{ account.comment }})
                                    </el-option>
                                </el-select>
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
                            :data="paginateData"
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
                    <div>
                        <el-pagination
                                background
                                @size-change="handleSizeChange"
                                @current-change="handleCurrentChange"
                                layout="sizes,prev, pager, next"
                                :current-page.sync="form.page"
                                :page-sizes="[20, 50, 100]"
                                :page-count="form.page_size"
                                :total="data.total">
                        </el-pagination>
                    </div>
                </template>

            </div>

        </el-card>

    </div>
</template>

<script>

    const start = new Date();
    start.setTime(start.getTime() - 3600 * 1000 * 24);

    export default {
        name    : "account-plan-sum",
        props   : {
            accounts: Array,
        },
        mounted() {
            this.form.id = this.accounts.map((item) => {
                return item.id;
            });

            this.handlePost();
        },
        data() {
            return {
                form         : {
                    id       : [],
                    dates    : [ start, start ],
                    page     : 1,
                    page_size: 20,
                },
                rules        : {
                    id   : [
                        { type: 'array', required: true, message: '请至少选择一个账户', trigger: 'change' },
                    ],
                    dates: [
                        { type: 'array', required: true, message: '请选择时间', trigger: 'change' }
                    ]
                },
                loading      : false,
                data         : null,
                isError      : false,
                pickerOptions: {
                    shortcuts: [ {
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
            paginateData() {
                return this.data ? this.data.data : [];
            }
        },
        methods : {
            async handlePost() {
                try {
                    this.isError = false;
                    this.loading = true;
                    let result   = await axios.post('/admin/jl-advertiser-plan-datas/account/list', this.form);
                    this.loading = false;
                    this.data    = result.data;

                } catch (e) {
                    this.loading = false;
                    this.isError = true;
                }
            },
            async onSubmit() {
                this.$refs[ 'form' ].validate((valid) => {
                    if (valid) {
                        this.handlePost();
                    }
                })

            },
            handleResetForm() {
                this.$refs[ 'form' ].resetFields();
            },
            handleSizeChange(val) {
                this.handlePost();
            },
            handleCurrentChange(val) {
                this.handlePost();
            }
        },
    }
</script>

<style scoped lang="less">
    .table-content {
        padding-top: 20px;
    }

    .filter-title {
        font-size: 20px;
        padding: 10px 0;
    }

</style>


<style>
    .page-account-plan-sum .el-select__tags {
        max-width: 100% !important;
    }
</style>
