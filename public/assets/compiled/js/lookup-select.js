class LookupSelect extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: "open" });

        // Création des styles
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
                    padding: 10px;
                    cursor: pointer;
                    color: #333333;
                    font-size: 12.8px;
                }
                .dropdown-item:hover {
                    background: #435ebe;
                    color: white;
                }
                .form-control {
                    display: block;
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #ccc;
                    border-radius: 5px;
                    outline: none;
                    background: white;
                }
            </style>
            <div class="custom-dropdown">
                <input type="text" class="form-control" placeholder="Sélectionner une option..." autocomplete="off">
                <div class="dropdown-list"></div>
            </div>
        `;

        this._value = null;
        this.selectedOption = null;
        this.options = [];
    }

    connectedCallback() {
        this.name = this.getAttribute("name") || "";
        this.id = this.getAttribute("id") || "";

        this.input = this.shadowRoot.querySelector("input");
        this.dropdownList = this.shadowRoot.querySelector(".dropdown-list");

        // Créer le champ hidden pour le backend Laravel
        this.hiddenInput = document.createElement("input");
        this.hiddenInput.type = "hidden";
        this.hiddenInput.name = this.name;
        this.appendChild(this.hiddenInput);

        this.input.placeholder = this.getAttribute("placeholder") || "Sélectionner une option...";

        if (this.hasAttribute("disabled")) {
            this.input.disabled = true;
        }

        this.loadOptionsFromDOM();

        this.observer = new MutationObserver(() => this.loadOptionsFromDOM());
        this.observer.observe(this, { childList: true });

        this.addEventListeners();
        this.dispatchEvent(new CustomEvent("ready", { bubbles: true }));
    }

    // 🔥 Modification ici : extraction des data-* depuis les options
    loadOptionsFromDOM() {
        this.options = Array.from(this.querySelectorAll("option")).map(option => {
            const customData = {};
            Array.from(option.attributes).forEach(attr => {
                if (attr.name.startsWith("data-")) {
                    const key = attr.name
                        .replace("data-", "")
                        .replace(/-([a-z])/g, (_, letter) => letter.toUpperCase()); // kebab-case -> camelCase
                    customData[key] = attr.value;
                }
            });

            return {
                value: option.value,
                text: option.textContent,
                ...customData
            };
        });

        this.populateDropdown();
    }

    populateDropdown() {
        this.dropdownList.innerHTML = "";
        this.options.forEach(option => {
            const div = document.createElement("div");
            div.className = "dropdown-item";
            div.textContent = option.text;
            div.dataset.value = option.value;
            div.tabIndex = -1;
            div.onclick = () => this.selectOption(option);
            this.dropdownList.appendChild(div);
        });
    }

    addEventListeners() {
        this.input.addEventListener("focus", () => this.showDropdown());

        this.input.addEventListener("input", () => {
            const filter = this.input.value.toLowerCase();
            Array.from(this.dropdownList.children).forEach(item => {
                item.style.display = item.textContent.toLowerCase().includes(filter) ? "block" : "none";
            });
            this.showDropdown();
        });

        this.input.addEventListener("keydown", e => {
            const items = Array.from(this.dropdownList.querySelectorAll(".dropdown-item"));
            const currentIndex = items.findIndex(item => item.classList.contains("active"));

            if (e.key === "ArrowDown") {
                e.preventDefault();
                const nextIndex = currentIndex + 1 < items.length ? currentIndex + 1 : 0;
                this.setActiveItem(items, nextIndex);
            } else if (e.key === "ArrowUp") {
                e.preventDefault();
                const prevIndex = currentIndex - 1 >= 0 ? currentIndex - 1 : items.length - 1;
                this.setActiveItem(items, prevIndex);
            } else if (e.key === "Enter") {
                e.preventDefault();
                if (currentIndex >= 0) items[currentIndex].click();
            } else if (e.key === "Escape") {
                this.hideDropdown();
            }
        });

        document.addEventListener("click", (event) => {
            if (!this.contains(event.target) && !this.shadowRoot.contains(event.target)) {
                this.hideDropdown();
            }
        });
    }

    setActiveItem(items, index) {
        items.forEach(item => item.classList.remove("active"));
        items[index].classList.add("active");
        items[index].scrollIntoView({ block: "nearest" });
    }

    selectOption(option) {
        this.selectedOption = option;
        this.input.value = option.text;
        this._value = option.value;
        this.setAttribute("value", option.value);
        this.hiddenInput.value = option.value; // 🔥 important pour Laravel
        this.dispatchChangeEvent();
        this.hideDropdown();
    }

    setSelectedValue(value) {
        const option = this.options.find(opt => opt.value === value);
        if (option) {
            this.selectOption(option);
        }
    }

    showDropdown() {
        this.dropdownList.style.display = "block";
    }

    hideDropdown() {
        this.dropdownList.style.display = "none";
    }

    get value() {
        return this._value;
    }

    set value(newValue) {
        this._value = newValue;
        const option = this.options.find(opt => opt.value === newValue);
        if (option) {
            this.input.value = option.text;
            this.selectedOption = option;
        }
    }

    // ✅ Donne accès à tous les attributs : value, text, codePays, codeRattachement, etc.
    getSelected() {
        return this.selectedOption;
    }

    setOptions(optionList) {
        this.options = optionList.map(opt => ({
            value: opt.value,
            text: opt.text,
            ...opt
        }));
        this.populateDropdown();
    }

    dispatchChangeEvent() {
        this.dispatchEvent(new Event("change", { bubbles: true }));
    }
    clear() {
        this.input.value = "";
        this._value = null;
        this.selectedOption = null;
        this.hiddenInput.value = "";
    }
    
}

customElements.define("lookup-select", LookupSelect);
