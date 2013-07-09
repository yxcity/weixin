$(document).ready(function() {
	//店铺二维码分享
	$(".toshare a").click(function() {
		$(".toshare p").toggle();
		$(".toshare a .gt").toggleClass("current");
	});

	$(".c_list dl dt a").click(function() {
		$(this).toggleClass("current");
		$(this).parent().next().toggle();
	})

	$(".c_list dl dd a").click(function() {
		$(this).addClass("current");
	})

	//商品数量
	var minus = $(".minus");
	var plus = $(".plus");
	minus.click(function() {
		if($('.num').val() != 1) {
			var num = $('.num').val();
			num -= 1;
			$('.num').val(num);
			total_fee(num);
		}
	})

	plus.click(function() {
		var num = $('.num').val();
		var product_nums = $('.product_nums').val();
		num = parseInt(num);
		if(num == parseInt(product_nums)) {
			alert('商品库存为：' + product_nums + '，订购量不能超过库存！');
			return false;
		}
		num += 1;
		$('.num').val(num);
		total_fee(num);
	})

	//计算总价
	function total_fee(num) {
		var price = parseFloat($('.product_price').val());
		$('.total_fee').html('￥' + toDecimal(price * num));
	}
});

//功能：将浮点数四舍五入，取小数点后2位  
function toDecimal(x) {  
    var f = parseFloat(x);  
    if (isNaN(f)) {  
        return;  
    }  
    f = Math.round(x*100)/100;  
    return f;  
}  