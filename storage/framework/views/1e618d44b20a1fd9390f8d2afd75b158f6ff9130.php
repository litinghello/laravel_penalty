<?php $__env->startSection('content_header'); ?>
    <h1>违章查询</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <script type="text/javascript" src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<?php echo $__env->yieldSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                
                <div class="card-body">
                    <div class="form-group input-group ">
                        <div class="input-group-addon">车牌号码</div>
                        <div class="input-group-addon">
                            <select id="violate_car_number_province" class="selectpicker" data-style="btn-info">
                            </select>
                        </div><input type="text" class="form-control" id="violate_car_number" placeholder="" value="A5F795">
                    </div>
                    <div class="form-group input-group">
                        <div class="input-group-addon" >车辆类型</div>
                        <div class="input-group">
                            <div class="form-control">
                                <select id="violate_car_number_type" class="selectpicker" data-style="btn-info">
                                    <option value="02">小型汽车</option>
                                    <option value="02">大型汽车</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group input-group">
                        <div class="input-group-addon">车架号码</div>
                        <input type="text" class="form-control" id="violate_car_frame_number" placeholder="后六位" value="010304">
                    </div>
                    <div class="form-group input-group">
                        <div class="input-group-addon">发动机号</div>
                        <input type="text" class="form-control" id="violate_car_engine_number" placeholder="后六位" value="010304">
                    </div>
                    <div class="form-group">
                        <div class="text-center">
                            <a id="violate_inquire_button" type="button" class="btn btn-primary">
                                <?php echo e(__('查询')); ?>

                            </a>
                        </div>
                    </div>
                    <?php $__env->startComponent('layouts.datatables'); ?>
                    <?php echo $__env->renderComponent(); ?>
                    <?php $__env->startComponent('layouts.modal'); ?>
                    <?php echo $__env->renderComponent(); ?>
                    <?php $__env->startComponent('layouts.wechat'); ?>
                    <?php echo $__env->renderComponent(); ?>
                </div>
                <script type="text/javascript">
                    $(document).ready(function() {
                        let info_object = {
                            'penalty_number':'违章信息',
                            'penalty_user_name':'违章代码',
                            'penalty_car_number':'违章时间',
                            'penalty_car_type':'违章地点',
                            'penalty_car_sss':'罚款金额（元）',
                            'penalty_phone_number':'手续费',
                            'penalty_phone_ddd':'扣分（仅供参考）',
                        };
                        var province_array=["川","渝","鄂","豫","皖","云","吉","鲁","沪","陕","京","湘","宁","津","粤","新","冀","晋","辽","黑","赣","桂","琼","藏","甘","青","闽","蒙","贵","苏","浙"];
                        province_array.forEach(function(value){
                            $("#violate_car_number_province").append("<option value='"+value+"'>"+value+"</option>");
                        });

                        $("#violate_inquire_button").click(function (){
                            let post_data = {
                                violate_car_number_province:$("#violate_car_number_province").val(),
                                violate_car_number:$("#violate_car_number").val(),
                                violate_car_number_type:$("#violate_car_number_type").val(),
                                violate_car_frame_number:$("#violate_car_frame_number").val(),
                                violate_car_engine_number:$("#violate_car_engine_number").val(),
                            };
                            console.log(post_data);
                            /*
                            $.ajax({
                                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                url:"<?php echo e(route('violates.info')); ?>",type:"POST",data:post_data,
                                success:function(data){
                                    if(data['status'] === 0){
                                        user_datatables_init(info_object,data['data'],function (data) {
                                            user_modal_input("手机号码",function (value) {
                                                let pay_value={
                                                    order_money:data.penalty_money+data.penalty_money_late+10,
                                                    order_src_type:"violate",
                                                    order_src_id:data.penalty_number,
                                                    order_phone_number:value,
                                                };
                                                user_modal_hide();//关闭弹出框
                                                user_wechat_pay(pay_value);
                                            });
                                        });
                                        user_datatables_show();
                                        $("#card_body_input").hide();
                                    }else{
                                        user_modal_warning(data['data']);
                                    }
                                },
                                error:function(error){
                                    user_modal_warning("请再次提交");
                                }
                            });
                            */
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>