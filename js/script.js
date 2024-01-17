// En script.js
$(document).ready(function() {
    $("form").on("submit", function(event) {
        event.preventDefault();
        
        $.ajax({
            type: "POST",
            url: "create.php",
            data: $(this).serialize(),
            success: function(response) {
            }
        });
    });
});
