console.log("Script loaded");

$(document).ready(function() {
  // Cache le formulaire d'inscription au chargement de la page
  $(".register-box").hide();

  // Événement clic sur le lien "Create your Amazon account"
  $("#register-link").on("click", function(e) {
    e.preventDefault();
    $(".login-box").hide();
    $(".register-box").show();
  });

  // Événement clic sur le lien "Already have an account? Sign In"
  $("#login-link").click(function() {
        location.reload();
      $(".register-box").fadeOut("fast", function() {
          $(".login-box").fadeIn("fast");
      });
  });

  // Soumission du formulaire d'inscription via AJAX
  $(".register-box form").on("submit", function(e) {
    //console.log("Form submitted")
      e.preventDefault();

      var name = $("#name").val();
      var email = $("#email-register").val();
      var password = $("#password-register").val();
      var passwordConfirm = $("#password-confirm").val();

      //console.log(name, email, password);

      if (password !== passwordConfirm) {
          alert("Passwords do not match.");
          return;
      }

      $.ajax({
          url: "register.php",
          method: "POST",
          data: {
              name: name,
              email: email,
              password: password
          },
          success: function(response) {
              // Traiter la réponse du serveur (par exemple, afficher un message de succès ou d'erreur)
              console.log(response);
              if (response.trim() === "Registration successful. Please verify your email address.") {
                  alert("Registration successful. Please verify your email address. in js");
                  $(".register-box").fadeOut("fast", function() {
                      $(".login-box").fadeIn("fast");
                      console.log("1");
                  });
              } else {
                  //alert("Registration failed. Please try again. in js");
                  //console.log("2");
              }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            //alert("An error occurred while registering. Please try again later. \n\nError: " + textStatus + ", " + errorThrown + "\nResponse: " + jqXHR.responseText);
        }
        
      });
  });
});


