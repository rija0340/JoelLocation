// $(document).ready(function () {
//     let hasError = true;
//     window.paypal.Buttons({
//         style: {
//             shape: "rect",
//             layout: "vertical"
//         },
//         createOrder: async function () {
//             const response = await fetch('/espaceclient/payment/create-order', {
//                 method: 'POST',
//                 headers: {
//                     'Content-Type': 'application/json'
//                 },
//                 body: JSON.stringify(
//                     {
//                         amount: 100, // Your amount variable
//                         devisId: "devis001" // Your devis ID variable
//                     }
//                 )
//             });

//             const order = await response.json();
//             return order.id;
//         },
//         // onApprove: async function (data, actions) {
//         //     const response = await fetch(`/espaceclient/payment/capture/${data.orderID
//         //         }`, { method: 'POST' });

//         //     const captureData = await response.json();
//         //     console.log("captureData");
//         //     console.log(captureData);
//         //     if (captureData.id) { // Payment successful - redirect or show success message
//         //         // window.location.href = '/payment/success';
//         //         window.location.href = '/';
//         //     }
//         // },
//         onApprove: async function (data, actions) {
//             try {
//                 // 1. Capture le paiement
//                 const captureResponse = await fetch(`/espaceclient/payment/capture/${data.orderID}`, {
//                     method: 'POST'
//                 });
//                 const captureData = await captureResponse.json();

//                 if (!captureData.id) {
//                     throw new Error(captureData.error || 'Échec de la capture du paiement');
//                 }

//                 // 2. Envoie au contrôleur de succès avec les données nécessaires
//                 const successResponse = await fetch('/espaceclient/payment/success', {
//                     method: 'POST',
//                     headers: {
//                         'Content-Type': 'application/json',
//                     },
//                     body: JSON.stringify({
//                         paypalOrderId: data.orderID,
//                         transactionId: captureData.id,
//                         paymentData: captureData.data
//                     })
//                 });

//                 // 3. Redirige selon la réponse du contrôleur
//                 if (successResponse.redirected) {
//                     window.location.href = successResponse.url;
//                 } else {
//                     const result = await successResponse.json();
//                     if (result.error) {
//                         throw new Error(result.error);
//                     }
//                     // Fallback si pas de redirection
//                     window.location.href = '/payment/success';
//                 }
//             } catch (error) {
//                 console.error('Erreur lors du paiement:', error);
//                 resultMessage('Une erreur est survenue lors du paiement. Veuillez réessayer.');
//             }
//         },
//         onError: function (err) {
//             console.error('PayPal error:', err);
//             // Handle error appropriately
//         }
//     }).render("#paypal-button-container");

//     function resultMessage(message) {
//         const container = document.querySelector("#result-message");
//         container.innerHTML = message;
//     }
// });


$(document).ready(function () {
    // Get references to key elements
    const cgvCheckbox = $('#conditionGeneralVente');
    const paypalContainer = $('#paypal-button-container');
    const submitButton = $('#validerPaiement');
    submitButton.hide();

    // Initially hide the PayPal container
    paypalContainer.hide();

    // Listen for changes on the CGV checkbox
    cgvCheckbox.change(function () {
        if ($(this).is(':checked')) {
            // Validate form data when CGV is checked
            if (validateFormData()) {
                // Show PayPal buttons and hide submit button when form is valid
                paypalContainer.show();
            } else {
                // If form is invalid, uncheck the CGV checkbox
                $(this).prop('checked', false);
            }
        } else {
            // Hide PayPal buttons and show submit button when CGV is unchecked
            paypalContainer.hide();
        }
    });

    // Initialize PayPal buttons
    window.paypal.Buttons({
        style: {
            shape: "rect",
            layout: "vertical"
        },
        createOrder: async function () {
            // Get amount and devisId from hidden fields
            const amount = $('#montant').val();
            const devisId = $('#devisId').val();

            // Create order via API
            const response = await fetch('/espaceclient/payment/create-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    amount: amount,
                    devisId: devisId
                })
            });

            const order = await response.json();
            if (order.error) {
                throw new Error(order.error);
            }
            return order.id;
        },
        onApprove: async function (data, actions) {
            try {
                // 1. Capture le paiement
                const captureResponse = await fetch(`/espaceclient/payment/capture/${data.orderID}`, {
                    method: 'POST'
                });
                const captureData = await captureResponse.json();

                if (!captureData.id) {
                    throw new Error(captureData.error || 'Échec de la capture du paiement');
                }

                // 2. Envoie au contrôleur de succès avec les données nécessaires
                const successResponse = await fetch('/espaceclient/payment/success', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        paypalOrderId: data.orderID,
                        transactionId: captureData.id,
                        paymentData: captureData.data
                    })
                });

                // 3. Redirige selon la réponse du contrôleur
                if (successResponse.redirected) {
                    window.location.href = successResponse.url;
                } else {
                    const result = await successResponse.json();
                    if (result.error) {
                        throw new Error(result.error);
                    }
                    // Fallback si pas de redirection
                    window.location.href = '/payment/success';
                }
            } catch (error) {
                console.error('Erreur lors du paiement:', error);
                resultMessage('Une erreur est survenue lors du paiement. Veuillez réessayer.');
            }
        },
        onError: function (err) {
            console.error('PayPal error:', err);
            resultMessage('Une erreur est survenue avec PayPal. Veuillez réessayer ou contacter le support.');
        }
    }).render("#paypal-button-container");

    // Submit button click handler
    submitButton.click(function (e) {
        e.preventDefault();

        if (!cgvCheckbox.is(':checked')) {
            alert('Veuillez accepter les conditions générales de location');
            return;
        }

        if (validateFormData()) {
            // Show PayPal buttons and hide submit button
            paypalContainer.show();
            submitButton.hide();
        }
    });

    // Helper function to validate form data
    function validateFormData() {
        // Get form data
        const nom = $('#client_info_nom').val();
        const prenom = $('#client_info_prenom').val();
        const sexe = $('#client_info_sexe').val();
        const portable = $('#client_info_portable').val();
        const mail = $('#client_info_mail').val();

        // Check if required fields are filled
        if (!nom || !prenom || !sexe || !portable || !mail) {
            alert('Veuillez remplir tous les champs obligatoires');
            return false;
        }

        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(mail)) {
            alert('Veuillez entrer une adresse email valide');
            return false;
        }

        // Phone number validation (basic)
        const phoneRegex = /^[0-9+\s]{8,15}$/;
        if (!phoneRegex.test(portable)) {
            alert('Veuillez entrer un numéro de téléphone valide');
            return false;
        }

        return true;
    }

    function resultMessage(message) {
        // Create message container if it doesn't exist
        if (!$('#result-message').length) {
            $('<div id="result-message" class="alert mt-3"></div>').insertAfter(paypalContainer);
        }

        const container = $('#result-message');
        container.html(message);

        if (message.includes('erreur')) {
            container.removeClass('alert-success').addClass('alert-danger');
        } else {
            container.removeClass('alert-danger').addClass('alert-success');
        }
    }
});
