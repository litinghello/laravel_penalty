<?php $__env->startSection('content_header'); ?>
    <h1>支付订单</h1>
<?php $__env->stopSection(); ?>
<?php $__env->startComponent('layouts.resources'); ?>
<?php echo $__env->renderComponent(); ?>
<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                
                <div class="card-body">
                    <?php if(session('status')): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo e(session('status')); ?>

                        </div>
                    <?php endif; ?>
                    <?php $__env->startComponent('layouts.datatables'); ?>
                    <?php echo $__env->renderComponent(); ?>
                    <?php $__env->startComponent('layouts.modal'); ?>
                    <?php echo $__env->renderComponent(); ?>
                    <?php $__env->startComponent('layouts.wechat'); ?>
                    <?php echo $__env->renderComponent(); ?>
                        <script type="text/javascript">
                            let info_object = {
                                'order_number':'订单号',
                                'order_money':'订单金额',
                                'order_phone_number':'联系电话',
                                'order_status':'状态',
                                'updated_at':'时间',
                            };
                            var order_status={
                                'invalid':"无效",
                                'unpaid':'未支付',
                                'paid':'已支付',
                                'processing':'正在处理',
                                'completed':'处理完成',
                            };
                            $(document).ready(function() {
                                $.ajax({
                                    type:"POST",
                                    headers: {'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>"},
                                    url:"<?php echo e(route('order.get')); ?>",
                                    data:"",
                                    success:function(data){
                                        if(data['status'] === 0){
                                            data['data'].forEach(function (value) {
                                                value.order_status = order_status[value.order_status];
                                            });
                                            user_datatables_init(info_object,data['data'],function (data) {
                                                let html = "<div>金额:"+data['order_money']+"元</div>"+
                                                            "<div>电话:"+data['order_phone_number']+"</div>"+"<br>是否确认支付？";
                                                user_modal_comfirm(html,function () {
                                                    // user_modal_warning("订单处理");
                                                    // console.log(data);
                                                    let pay_value={
                                                        order_money:parseInt(data.order_money),
                                                        order_src_type:data.order_src_type,
                                                        order_src_id:data.order_src_id,
                                                        order_phone_number:data.order_phone_number,
                                                    };
                                                    // user_modal_hide();//关闭弹出框
                                                    user_wechat_pay(pay_value);
                                                });
                                            });
                                            user_datatables_show();
                                        }else{
                                            user_modal_warning(data['data']);
                                        }
                                    },
                                    error:function(error){
                                        user_modal_warning("请再次提交");
                                    }
                                });
                            });
                        </script>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('adminlte::page', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>