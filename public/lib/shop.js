$(function(){
							$('#shopname').blur(function(){
								$('#group-shopname').removeClass('success');
			                    $('#group-shopname').removeClass('warning');
								var shopname = $('#shopname').val();
								if (shopname=='')
								{
									$('#group-shopname').addClass('warning');
									$('#help-shopname').html('请输入门店名称');
								}else
								{
									$('#group-shopname').addClass('success');
									$('#help-shopname').html('正确');
								}
								});
							});
							$('#address').blur(function(){
								$('#group-address').removeClass('success');
			                    $('#group-address').removeClass('warning');
								var address = $('#address').val();
								if (address=='')
								{
									$('#group-address').addClass('warning');
									$('#help-address').html('请输入门店地址');
								}else
								{
									$('#group-address').addClass('success');
									$('#help-address').html('正确');
								}
								});
							$('#tel').blur(function(){
								$('#group-tel').removeClass('success');
			                    $('#group-tel').removeClass('warning');
								var tel = $('#tel').val();
								if (tel=='')
								{
									$('#group-tel').addClass('warning');
									$('#help-tel').html('请输入门店电话');
								}else
								{
									$('#group-tel').addClass('success');
									$('#help-tel').html('正确');
								}
								});
							$('#content').blur(function(){
								$('#group-content').removeClass('success');
			                    $('#group-content').removeClass('warning');
								var content = $('#content').val();
								if (content=='')
								{
									$('#group-content').addClass('warning');
									$('#help-content').html('请输入门店描述');
								}else
								{
									$('#group-content').addClass('success');
									$('#help-content').html('正确');
								}
								});
							
			            function clickForm(){
							var shopname = $('#shopname').val();
							if (shopname=='')
							{
			                    $('#group-shopname').addClass('warning');
								$('#help-shopname').html('请输入门店名称');
								return false;
							}
							var address = $('#address').val();
							if (address=='')
							{
			                    $('#group-address').addClass('warning');
								$('#help-address').html('请输入门店地址');
								return false;
							}
							var tel = $('#tel').val();
							if (tel=='')
							{
			                    $('#group-tel').addClass('warning');
								$('#help-tel').html('请输入门店电话');
								return false;
							}
							var content = $('#content').val();
							if (content=='')
							{
			                    $('#group-content').addClass('warning');
								$('#help-content').html('请输入门店描述');
								return false;
							}
							}