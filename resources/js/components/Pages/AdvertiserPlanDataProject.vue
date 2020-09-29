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
                        <el-col :span="12">
                            <el-form-item label="医院类型" prop="hospital_id">
                                <el-select v-model="form.hospital_id" placeholder="请选择">
                                    <el-option
                                            v-for="(value, key) in hospitalList"
                                            :key="key"
                                            :label="value"
                                            :value="key">
                                    </el-option>
                                </el-select>
                            </el-form-item>

                        </el-col>
                    </el-row>

                    <div class="submit-field">
                        <el-button size="mini" type="primary" @click="onSubmit">
                            <i class="el-icon-search"></i>
                            查询
                        </el-button>
                        <el-button size="mini" @click="handleResetForm">
                            <i class="el-icon-refresh"></i>
                            重置
                        </el-button>
                    </div>
                    <el-row>
                        <el-col :span="20">
                            <el-form-item label="品项">
                                <el-tag
                                        :key="tag"
                                        v-for="tag in dynamicTags"
                                        closable
                                        :disable-transitions="false"
                                        @close="handleClose(tag)">
                                    {{tag}}
                                </el-tag>
                                <el-input
                                        class="input-new-tag"
                                        v-if="inputVisible"
                                        v-model="inputValue"
                                        ref="saveTagInput"
                                        size="small"
                                        @keyup.enter.native="handleInputConfirm"
                                        @blur="handleInputConfirm"
                                >
                                </el-input>
                                <el-button v-else class="button-new-tag" size="small" @click="showInput">+ 品项
                                </el-button>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </el-form>
            </div>
            <div class="table-content">
                <div v-if="!dynamicTags || !dynamicTags.length">
                    请添加病种以分类消费
                </div>
                <template v-else-if="data">
                    <el-table
                            :data="projectData"
                            border
                            style="width: 100%">
                        <el-table-column
                                prop="comment_name"
                                fixed
                                label="品"
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
                                prop="rebate_cost"
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

    import { round } from "../../Utils/common";

    const today       = new Date();
    const tagsKey     = '_TAGS_PROJECT_';
    const DefaultItem = {
        show               : 0,
        click              : 0,
        cost               : 0,
        rebate_cost        : 0,
        attribution_convert: 0,
    };

    export default {
        name    : "advertiser-plan-data-project",
        props   : {
            hospitalList: Object,
        },
        data() {
            return {
                loading      : false,
                data         : null,
                isError      : false,
                projectList  : [],
                dynamicTags  : [],
                inputVisible : false,
                inputValue   : '',
                form         : {
                    dates      : [ today, today ],
                    hospital_id: null,
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
        mounted() {
            this.$set(this, 'dynamicTags', this.getTags());
            console.log('this.hospitalList :', this.hospitalList);

            this.handlePost();
        },
        computed: {
            projectData() {
                let tags = this.dynamicTags;
                if (!this.data || !tags || !tags.length) return [];

                let result = {};
                console.log('tags :',);
                tags.forEach((item) => {
                    result[ item ] = {
                        comment_name: item,
                        ...DefaultItem
                    };
                });
                result[ 'other' ] = {
                    comment_name: '其他',
                    ...DefaultItem
                };
                result[ 'total' ] = {
                    comment_name: '合计',
                    ...DefaultItem
                };


                let addItem = (item, data) => {
                    result[ item ][ 'show' ] += data[ 'show' ];
                    result[ item ][ 'click' ] += data[ 'click' ];
                    result[ item ][ 'cost' ] += parseFloat(data[ 'cost' ]);
                    result[ item ][ 'rebate_cost' ] += parseFloat(data[ 'rebate_cost' ]);
                    result[ item ][ 'attribution_convert' ] += data[ 'attribution_convert' ];
                }

                this.data.forEach((data) => {
                    let match = false;
                    for (let i = 0 ; i < tags.length ; i++) {
                        let item = tags[ i ];
                        let reg  = new RegExp(item);
                        if (reg.test(data.ad_name)) {
                            addItem(item, data);
                            match = true;
                            continue;
                        }
                    }
                    if (!match) addItem('other', data);
                    addItem('total', data);
                });

                return Object.values(result).map(($account) => {
                    $account[ 'cost' ]                     = round($account[ 'cost' ], 3);
                    $account[ 'rebate_cost' ]              = round($account[ 'rebate_cost' ], 3);
                    $account[ 'ctr' ]                      = $account[ 'show' ] ? round($account[ 'click' ] / $account[ 'show' ] * 100, 3) + '%' : 0;
                    $account[ 'avg_show_cost' ]            = $account[ 'show' ] ? round($account[ 'cost' ] / $account[ 'show' ] * 1000, 3) + '元' : 0;
                    $account[ 'avg_click_cost' ]           = $account[ 'click' ] ? round($account[ 'cost' ] / $account[ 'click' ], 3) + '元' : 0;
                    $account[ 'attribution_convert_cost' ] = $account[ 'attribution_convert' ] ? round($account[ 'cost' ] / $account[ 'attribution_convert' ], 3) + '元' : 0;
                    return $account;
                });
            }
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
            handleClose(tag) {
                this.dynamicTags.splice(this.dynamicTags.indexOf(tag), 1);
            },

            showInput() {
                this.inputVisible = true;
                this.$nextTick(_ => {
                    this.$refs.saveTagInput.$refs.input.focus();
                });
            },
            handleInputConfirm() {
                let inputValue = this.inputValue;
                if (inputValue) {
                    this.dynamicTags.push(inputValue);
                }
                this.inputVisible = false;
                this.inputValue   = '';
            },
            syncTags() {
                let tags = this.dynamicTags;
                localStorage.setItem(tagsKey, JSON.stringify(tags));
            },
            getTags() {
                let value = localStorage.getItem(tagsKey);
                return value ? JSON.parse(value) : [];
            }
        },
        watch   : {
            dynamicTags() {
                this.syncTags();
            }
        }
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

    .submit-field {
        padding-bottom: 20px;
    }

</style>
<style>
    .el-tag + .el-tag {
        margin-left: 10px;
    }

    .button-new-tag {
        margin-left: 10px;
        height: 32px;
        line-height: 30px;
        padding-top: 0;
        padding-bottom: 0;
    }

    .input-new-tag {
        width: 90px;
        margin-left: 10px;
        vertical-align: bottom;
    }
</style>
