class LookupMultiSelect extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: "open" });

        this.shadowRoot.innerHTML = `
        <style>
            .custom-dropdown {
                position: relative;
                width: 100%;
            }
            .dropdown-list {
                width: auto;
                background: white;
                border: 1px solid #ccc;
                border-radius: 5px;
                max-height: 200px;
                overflow-y: auto;
                display: none;
                box-shadow: 0px 4px 6px rgba(0,0,0,0.1);
            }
            .dropdown-item {
                display: flex;
                align-items: center;
                padding: 10px;
                cursor: pointer;
                color: #333333;
                font-size: 12.8px;
                background: white;
            }
            .dropdown-item:hover, .dropdown-item.selected {
                background: #435ebe;
                color: white;
            }
            .dropdown-item input {
                margin-right: 10px;
            }
            .selected-items {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                border: 1px solid #ccc;
                border-radius: 5px;
                padding: 5px;
                min-height: 23px;
                cursor: text;
                background: white;
                color: #333333;
                position: relative;
            }
            .form-control {
                border: none;
                outline: none;
                width: 100%;
                padding: 5px;
            }
            .select-all {
                display: flex;
                align-items: center;
                padding: 10px;
                color: #333333;
                border-bottom: 1px solid #ccc;
                font-weight: bold;
                cursor: pointer;
            }
            #select-all {
                margin-right: 10px;
                color: #333333;
            }
            .table-custom {
                    width: 100%;
                    border-collapse: collapse;
                    background: white;
                }
                .table-custom tr {
                    border-bottom: 1px solid #ddd;
                    background:rgb(183, 180, 209);

                }
                .table-custom td {
                    padding: 10px;
                    text-align: left;
                    color: #333;
                    font-size: 13px;
                }
                .table-custom span {
                    color: red;
                    font-size: 16px;
                    cursor: pointer;
                    font-weight: bold;
                }
                .table-custom span:hover {
                    color: darkred;
                }
        </style> 
        <div class="custom-dropdown">
            <div class="selected-items" tabindex="0">
                <input type="text" class="form-control" style="color: black;" placeholder="Sélectionner des options..." autocomplete="off">
            </div>
            <div class="dropdown-list">
                <div class="select-all">
                    <input type="checkbox" id="select-all" style="color: black;"> Tout sélectionner
                </div>
            </div>
        </div>
        `;

        this.options = [];
        this.selectedOptions = [];
    }

    connectedCallback() {
        this.name = this.getAttribute("name") || "";
        this.id = this.getAttribute("id") || "";
    
        this.input = this.shadowRoot.querySelector(".form-control");
        this.dropdownList = this.shadowRoot.querySelector(".dropdown-list");
        this.selectedContainer = this.shadowRoot.querySelector(".selected-items");
        this.selectAllCheckbox = this.shadowRoot.querySelector("#select-all");
        this.hiddenInputs = [];
    
        // 👇 Ajoute ça ici
        this.hiddenInput = document.createElement("input");
        this.hiddenInput.type = "hidden";
        this.hiddenInput.name = this.name;
        this.appendChild(this.hiddenInput);
    
        this.loadInitialOptions();
        this.populateDropdown();
        this.addEventListeners();
    
        setTimeout(() => {
            if (this.hiddenInputs.length > 0) {
                const current = this.hiddenInputs.map(input => input.value);
                this.setSelectedValues(current);
            }
        }, 200);
    }
    

    loadInitialOptions() {
        this.options = Array.from(this.querySelectorAll("option")).map(option => ({
            value: option.value,
            text: option.textContent
        }));
    }

    setOptions(optionList) {
        this.options = optionList.map(opt => ({
            value: opt.value,
            text: opt.text
        }));
        this.selectedOptions = []; // reset selection
        this.populateDropdown();
        this.updateSelectedDisplay();
    }
    setSelectedValues(values) {
        // Vérifie que `values` est bien un tableau
        if (!Array.isArray(values)) return;
    
        // Match même si string/number
        this.selectedOptions = this.options.filter(opt =>
            values.some(v => v == opt.value)
        );
    
        this.updateSelectedDisplay();
        this.populateDropdown();
    
        // Met à jour l'input hidden
        const selectedValues = this.selectedOptions.map(opt => opt.value);
        this.hiddenInput.value = selectedValues.join(',');
    }
    
    
    getSelected() {
        return this.selectedOptions;
    }

    populateDropdown() {
        const existingItems = this.shadowRoot.querySelectorAll(".dropdown-item");
        existingItems.forEach(item => item.remove());

        this.options.forEach(option => {
            const div = document.createElement("div");
            div.className = "dropdown-item";

            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.dataset.value = option.value;

            const isSelected = this.selectedOptions.some(sel => sel.value === option.value);
            checkbox.checked = isSelected;
            if (isSelected) div.classList.add("selected");

            checkbox.onchange = () => this.toggleOption(option, checkbox.checked);

            div.appendChild(checkbox);
            div.appendChild(document.createTextNode(option.text));
            this.dropdownList.appendChild(div);
        });
    }

    addEventListeners() {
        this.selectedContainer.addEventListener("click", () => {
            this.input.focus();
            this.showDropdown();
        });

        document.addEventListener("click", (event) => {
            if (!this.contains(event.target)) {
                this.hideDropdown();
            }
        });

        this.input.addEventListener("input", () => {
            const filter = this.input.value.toLowerCase();
            this.dropdownList.querySelectorAll(".dropdown-item").forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(filter) ? "flex" : "none";
            });
            this.showDropdown();
        });

        this.selectAllCheckbox.addEventListener("change", (event) => {
            this.toggleAll(event.target.checked);
        });
    }

    toggleOption(option, isChecked) {
        if (isChecked) {
            if (!this.selectedOptions.some(opt => opt.value === option.value)) {
                this.selectedOptions.push(option);
            }
        } else {
            this.selectedOptions = this.selectedOptions.filter(opt => opt.value !== option.value);
        }
        this.updateSelectedDisplay();
        this.populateDropdown(); // refresh state
        this.dispatchChangeEvent();
    }

    toggleAll(isChecked) {
        this.selectedOptions = isChecked ? [...this.options] : [];
        this.populateDropdown();
        this.updateSelectedDisplay();
        this.dispatchChangeEvent();
    }

    removeOption(option) {
        this.selectedOptions = this.selectedOptions.filter(opt => opt.value !== option.value);
        const checkbox = this.dropdownList.querySelector(`.dropdown-item input[data-value='${option.value}']`);
        if (checkbox) {
            checkbox.checked = false;
            checkbox.parentElement.classList.remove("selected");
        }
        this.updateSelectedDisplay();
        this.populateDropdown();
        this.dispatchChangeEvent();
    }

    updateSelectedDisplay() {
        this.selectedContainer.innerHTML = "";

        const table = document.createElement("table");
        table.className = "table-custom";

        const tbody = document.createElement("tbody");

        this.selectedOptions.forEach(option => {
            const row = document.createElement("tr");

            const textCell = document.createElement("td");
            textCell.textContent = option.text;

            const actionCell = document.createElement("td");
            const closeBtn = document.createElement("span");
            closeBtn.textContent = "❌";
            closeBtn.onclick = () => this.removeOption(option);
            actionCell.appendChild(closeBtn);

            row.appendChild(textCell);
            row.appendChild(actionCell);
            tbody.appendChild(row);
        });

        table.appendChild(tbody);
        this.selectedContainer.appendChild(table);
        this.selectedContainer.appendChild(this.input);
        this.input.value = "";

         // Supprimer les anciens champs
        this.hiddenInputs.forEach(input => input.remove());
        this.hiddenInputs = [];

        // Ajouter un champ hidden par valeur sélectionnée (comme un vrai select multiple)
        this.selectedOptions.forEach(opt => {
            const hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = this.name + "[]"; // 👈 tableau attendu par Laravel
            hidden.value = opt.value;
            this.appendChild(hidden);
            this.hiddenInputs.push(hidden);
        });

    }

    showDropdown() {
        this.dropdownList.style.display = "block";
    }

    hideDropdown() {
        this.dropdownList.style.display = "none";
    }

    get value() {
        return this.selectedOptions.map(opt => opt.value);
    }

    set value(newValues) {
        this.selectedOptions = this.options.filter(opt => newValues.includes(opt.value));
        this.updateSelectedDisplay();
        this.populateDropdown();
    }

    dispatchChangeEvent() {
        const event = new CustomEvent("change", {
            detail: {
                selectedValues: this.value,
                selectedObjects: this.getSelected()
            }
        });
        this.dispatchEvent(event);
    }
}

customElements.define("lookup-multiselect", LookupMultiSelect);
