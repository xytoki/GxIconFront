<style scoped>
.page-donate{
    text-align: center;
}
h1{
    font-size: 40px;
}
.qrCard {
    width: 200px;
    display: inline-block;
}

.qrCard .ivu-card-head p {
    height: 150px;
}
.qrCard .ivu-card-head img{
    width:150px;
    height: 150px;
}
.oneAlert{
    width:500px;
    max-width: 100%;
    margin: 0 auto;
}
</style>
<template>
    <div class="page-donate">
        <h1>暂时用爱发电</h1>
        <h3>图标存储 打包系统 日常维护 都需要耗费服务器资源也就是费用。希望各位支持，让共享图标包走的更远。无论多少，都是一点心意。</h3>
        <alert class="oneAlert">暂时用爱发电</alert>
            <Card class="qrCard">
                <p slot="title">
                    <img src="" alt="">
                </p>
                <p></p>
            </Card>
    </div>
</template>
<script>
    export default {
        data () {
            return {
            }
        },
        methods:{
        },
        created:function(){
            
        }
    }
</script>