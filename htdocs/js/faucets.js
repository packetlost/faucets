var bs_faucets = 
{
    checks: function(timeout)
    {
        if(typeof timeout == 'undefined')
        {
            timeout = 30000;
        }
        setTimeout(function () 
        {
            
        }, timeout);
    },
    check: function()
    {
        $('.balance').each(function(i)
        {
            var span = this;
            var chain = $(span).attr('data-chain');
            var address = $(span).attr('data-address');
            $.fn.blockstrap.api.balance(address, chain, function(results)
            {
                var balance = (parseInt(results) / 100000000).toFixed(8);
                $(span).text(balance);
            });
        });
    },
    claim: function()
    {
        $('.request-coins').on('submit', function(e)
        {
            e.preventDefault();
            var tx = false;
            var form = this;
            var bs = $.fn.blockstrap;
            var button = $(form).find('button[type="submit"]');
            var chain = $(form).attr('data-chain');
            var email = $(form).find('input[name="email"]').val();
            var code = $(form).find('input[name="code"]').val();
            var address = $(form).find('input[name="address"]').val();
            var return_address = $(form).find('input[name="return"]').val();
            var hidden = $(form).find('.toggle-box');
            if(email)
            {
                if(code && !address)
                {
                    var chain_name = $.fn.blockstrap.settings.blockchains[chain].blockchain;
                    bs.core.modal('Warning', chain_name + ' address required in order to send coins.');
                }
                else
                {
                    $(button).addClass('loading');
                    if(code)
                    {
                        $.fn.blockstrap.api.unspents(return_address, chain, function(unspents)
                        {
                            if($.isArray(unspents) && blockstrap_functions.array_length(unspents) > 0)
                            {   
                                var fee = $.fn.blockstrap.settings.blockchains[chain].fee * 100000000;

                                var inputs = [];
                                var outputs = [{
                                    'address': address,
                                    'value': fee * 10
                                }];
                                $.each(unspents, function(k, unspent)
                                {
                                    inputs.push({
                                        txid: unspent.txid,
                                        n: unspent.index,
                                        script: unspent.script,
                                        value: unspent.value,
                                    });
                                    //available_balance = available_balance + unspent.value;
                                });

                                var tx = $.fn.blockstrap.blockchains.raw(
                                    return_address, 
                                    false, 
                                    inputs, 
                                    outputs, 
                                    fee, 
                                    fee * 10, 
                                    false,
                                    false
                                );
                                
                                $.ajax({
                                    url: 'php/claim.php',
                                    data: {email: email, chain: chain, code: code, address: address, tx: tx},
                                    dataType: 'json',
                                    method: 'post',
                                    success: function(results)
                                    {
                                        $(button).removeClass('loading');
                                        if($(hidden).css('display') == 'none')
                                        {
                                            $(hidden).show(350);
                                        }
                                        var success = false;
                                        var title = 'Warning';
                                        var content = 'Unknown error.';
                                        if(typeof results.success != 'undefined' && results.success === true)
                                        {
                                            title = 'Success';
                                            success = true;
                                        }
                                        if(typeof results.msg != 'undefined' && results.msg)
                                        {
                                            content = results.msg;
                                        }
                                        $.fn.blockstrap.core.modal(title, content);
                                    }
                                });
                            }
                        });
                    }
                    else
                    {
                        $.ajax({
                            url: 'php/claim.php',
                            data: {email: email, chain: chain, code: code, address: address},
                            dataType: 'json',
                            method: 'post',
                            success: function(results)
                            {
                                $(button).removeClass('loading');
                                if($(hidden).css('display') == 'none')
                                {
                                    $(hidden).show(350);
                                }
                                var success = false;
                                var title = 'Warning';
                                var content = 'Unknown error.';
                                if(typeof results.success != 'undefined' && results.success === true)
                                {
                                    title = 'Success';
                                    success = true;
                                }
                                if(typeof results.msg != 'undefined' && results.msg)
                                {
                                    content = results.msg;
                                }
                                if(typeof results.return_address != 'undefined' && results.return_address)
                                {
                                    $(form).find('input[name="return"]').val(results.return_address);
                                }
                                bs.core.modal(title, content);
                            }
                        });
                    }
                }
            }
            else
            {
                bs.core.modal('Warning', 'Email address required.');
            }
        });
    },
    forms: function()
    {
        bs_faucets.claim();
        bs_faucets.check();
    },
    init: function()
    {
        bs_faucets.forms();
    }
};

$(document).ready(function()
{
    bs_faucets.init();
});

