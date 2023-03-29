function changeLabel(sel) {
  if((sel.options[sel.selectedIndex].text) == "Upcoming Order")
        {
            document.getElementById("request_label").innerText = "Order Number";
            document.getElementById("reference_number").placeholder = "Enter your order no.";
            document.getElementById('reference_number').type = 'number';
        }else if((sel.options[sel.selectedIndex].text) == "Product Inquiries") {
            document.getElementById("request_label").innerText = "Product Name (or) SKU";
            document.getElementById("reference_number").placeholder = "Enter either product name or SKU";
            document.getElementById('reference_number').type = 'text';
        }else if((sel.options[sel.selectedIndex].text) == "Product Complaints") {
            document.getElementById("request_label").innerText = "Product Name (or) SKU";
            document.getElementById("reference_number").placeholder = "Enter either product name or SKU";
            document.getElementById('reference_number').type = 'text';
        }else if((sel.options[sel.selectedIndex].text) == "Returns / Cancellation") {
            document.getElementById("request_label").innerText = "Order Number";
            document.getElementById("reference_number").placeholder = "Enter your order no.";
            document.getElementById('reference_number').type = 'number';
        }else if((sel.options[sel.selectedIndex].text) == "Damage / Shortages") {
            document.getElementById("request_label").innerText = "Order Number";
            document.getElementById("reference_number").placeholder = "Enter your order no.";
            document.getElementById('reference_number').type = 'number';
        }else if((sel.options[sel.selectedIndex].text) == "General Inquiries") {
            document.getElementById("request_label").innerText = "Subject";
            document.getElementById("reference_number").placeholder = "Enter the subject";
            document.getElementById('reference_number').type = 'text';
        }else if((sel.options[sel.selectedIndex].text) == "Profile Update") {
            document.getElementById("request_label").innerText = "Subject";
            document.getElementById("reference_number").placeholder = "Enter the subject";
            document.getElementById('reference_number').type = 'text';
        }
}