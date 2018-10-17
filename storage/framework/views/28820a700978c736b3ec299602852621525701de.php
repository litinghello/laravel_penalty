<?php $__env->startSection('content_header'); ?>
    <h1>支付订单</h1>
<?php $__env->stopSection(); ?>
<?php $__env->startComponent('layouts.resources'); ?>
<?php echo $__env->renderComponent(); ?>
<?php $__env->startSection('content'); ?>

    <?php if(!count($list)): ?>
        <p class="help-block text-center well">没 有 记 录 哦！</p>
    <?php else: ?>
        <div class="table-responsive table-bordered">

            <table class="table table-striped table-hover">
                <thead>
                <tr>
                <tr >
                    <th>订单号</th>
                    <th>订单金额</th>
                    <th>联系电话</th>
                    <th>状态</th>
                    <th>时间</th>
                </tr>
                </thead>
                <tbody>

                <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as &$data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr onclick="dataClick(<?php echo e(json_encode($data)); ?>)">
                        <td><?php echo e($data->order_number); ?></td>
                        <td><?php echo e($data->order_money); ?></td>
                        <td><?php echo e($data->order_phone_number); ?></td>
                        <td><?php echo e($data->order_status); ?></td>
                        <td><?php echo e($data->updated_at); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>

            </table>

            <?php if(isset($page)): ?><?php echo $page; ?><?php endif; ?>
        </div>
    <?php endif; ?>

<?php $__env->stopSection(); ?>


<?php $__env->startComponent('layouts.modal'); ?>
<?php echo $__env->renderComponent(); ?>
<?php $__env->startComponent('layouts.wechat'); ?>
<?php echo $__env->renderComponent(); ?>
<script>
    function dataClick(data){

        let html = "<div>金额:"+data['order_money']+"元</div>"+
            "<div>电话:"+data['order_phone_number']+"</div>"+"<br>是否确认支付？";
        user_modal_comfirm(html,function () {
            // user_modal_warning("订单处理");
             console.log(data);
            let pay_value={
                order_money:parseInt(data.order_money),
                order_src_type:data.order_src_type,
                order_src_id:data.order_src_id,
                order_phone_number:data.order_phone_number,
            };
            // user_modal_hide();//关闭弹出框
            user_wechat_pay(pay_value);
        });

    }
</script>



    
        
            
                
                
                    
                        
                            
                        
                    
                    
                    
                    
                    
                    
                    
                        
                            
                                
                                
                                
                                
                                
                            
                            
                                
                                
                                
                                
                                
                            
                            
                                
                                    
                                    
                                    
                                    
                                    
                                        
                                            
                                                
                                            
                                            
                                                
                                                            
                                                
                                                    
                                                    
                                                    
                                                        
                                                        
                                                        
                                                        
                                                    
                                                    
                                                    
                                                
                                            
                                            
                                        
                                            
                                        
                                    
                                    
                                        
                                    
                                
                            
                        
                
            
        
    





<?php echo $__env->make('adminlte::page', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>