<?php
if (!defined('APPLICATION'))
    exit();

/**
 *Insert this below the "Ask and question/disuccion" buttons
 *
 * This will peform a POST to your server asking for related threads as the user
 * types in his/her title
 *
 * @todo make it faster and work with Vanilla's default jquery autocomplete and NOT jqueryUI
 */
?>

<script>
    $(document).ready(function(){
        var WebRoot = $("#WebRoot").val();
        var current = $("#Form_Name").val();
        var past = $("#Form_Name").val();

        var finished = true;


        $('#Form_Name').keyup(function ()
        {
            waitForMsg();
            setTimeout(function() {$("#Test").animate({"top":"-=80px"})} , 1000); // delays 1s
        });

        function addmsg(type, msg){
            $("#Search").html(
            "<div class='msg Inner "+ type +"'><h4>Questions that may already have your answer: </h4>"+ msg +"</div>"
        );
        }


        function waitForMsg(){
            var length = $("#Form_Name").val().length;
            var Query = $("#Form_Name").val();

            if(length >= 3 && finished == true){ //only allow strings that are at least 3 characters AND different from last time
                finished = false;
                $.ajax({
                    dataType: "json",
                    type: "POST",
                    data: "Query="+Query,
                    url: WebRoot+"/plugin/sphinxsearch/newdiscussion",

                    success: function(data){ /* called when request to barge.php completes */
                        addmsg("new", data.Text); /* Add response to a .msg div (with the "new" class)*/
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown){
                        addmsg("error", textStatus + " (" + errorThrown + ")");

                    }
                });
                finished = true;

                //   past = $("#Form_Name").val();
            }
        };
    });

</script>


<style>
    #Search h4{
/*        margin: auto;*/
        max-width: 800px;
    }
    #Search_Container{
        height: 100px;
        overflow-y: auto;
        max-width: 800px;
        margin: auto;
        border: 2px dotted #333333;
        padding: 5px;
    }

</style>
<div id="Test"></div>
<div id="Search">
</div>
