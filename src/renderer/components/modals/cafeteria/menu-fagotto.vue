<template>
  <div v-show="isVisible" class="fagotto-overlay" @click.self="closeModal">
    <div class="fagotto-modal" :key="modalKey">

      <!-- Header -->
      <div class="fagotto-header">
        <div class="header-content">
          <div class="header-left">
            <div class="header-logo">
              <i class="fas fa-star"></i>
            </div>
            <div>
              <h3 class="header-title">Menú Fagotto</h3>
              <p class="header-subtitle">Arma tu combo personalizado</p>
            </div>
          </div>
          <div class="header-right">
            <div v-if="activeTab === 'bowl' ? currentPrice > 0 : activeTab === 'combo' ? comboPrice > 0 : activeTab === 'combo2' ? combo2Price > 0 : ciabattaPrice > 0" class="header-price-badge">
              ${{ formatNumber(activeTab === 'bowl' ? currentPrice : activeTab === 'combo' ? comboPrice : activeTab === 'combo2' ? combo2Price : ciabattaPrice) }}
            </div>
            <button @click="closeModal" class="btn-close-fagotto">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>

        <!-- Tabs -->
        <div class="fagotto-tabs">
          <button @click="switchTab('bowl')" :class="['fagotto-tab', { active: activeTab === 'bowl' }]">
            🍝 Bowl
          </button>
          <button @click="switchTab('combo')" :class="['fagotto-tab', { active: activeTab === 'combo' }]">
            🍽️ Combos
          </button>
          <button @click="switchTab('combo2')" :class="['fagotto-tab', { active: activeTab === 'combo2' }]">
            ⭐ Combo 2
          </button>
          <button @click="switchTab('ciabatta')" :class="['fagotto-tab', { active: activeTab === 'ciabatta' }]">
            🥖 Ciabatta
          </button>
        </div>

        <!-- Barra de progreso (Bowl) -->
        <div v-if="activeTab === 'bowl'" class="progress-bar-container">
          <div class="progress-step" :class="{ active: true, done: selectedPasta }">
            <span class="step-num">1</span>
            <span class="step-label">Pasta</span>
          </div>
          <div class="progress-line" :class="{ active: selectedPasta }"></div>
          <div class="progress-step" :class="{ active: selectedPasta, done: selectedSize }">
            <span class="step-num">2</span>
            <span class="step-label">Tamaño</span>
          </div>
          <div class="progress-line" :class="{ active: selectedSize }"></div>
          <div class="progress-step" :class="{ active: selectedSize, done: selectedSalsa }">
            <span class="step-num">3</span>
            <span class="step-label">Salsa</span>
          </div>
          <div class="progress-line" :class="{ active: selectedSalsa }"></div>
          <div class="progress-step" :class="{ active: selectedSalsa }">
            <span class="step-num">4</span>
            <span class="step-label">Extras</span>
          </div>
        </div>
      </div>

      <!-- Body -->
      <div class="fagotto-body">

        <!-- === MODO BOWL === -->
        <div v-show="activeTab === 'bowl'">

        <!-- PASO 1: Tipo de pasta -->
        <div class="step-section">
          <h4 class="step-title">
            <span class="step-badge">1</span>
            Elige tu pasta
          </h4>
          <div class="pasta-grid">
            <div
              v-for="pasta in PASTAS"
              :key="pasta.key"
              @click="selectPasta(pasta)"
              :class="['pasta-card', { selected: selectedPasta && selectedPasta.key === pasta.key }]">
              <div class="pasta-emoji">{{ pasta.emoji }}</div>
              <div class="pasta-name">{{ pasta.name }}</div>
              <div v-if="selectedPasta && selectedPasta.key === pasta.key" class="selected-check">
                <i class="fas fa-check-circle"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- PASO 2: Tamaño -->
        <transition name="slide-down">
          <div v-if="selectedPasta" class="step-section">
            <h4 class="step-title">
              <span class="step-badge">2</span>
              Elige tu tamaño
            </h4>
            <div class="sizes-grid">
            <div
              v-for="size in SIZES"
              :key="size.key"
              @click="selectSize(size)"
              :class="['size-card', { selected: selectedSize && selectedSize.key === size.key }]">
              <div class="size-icon">
                <i class="fas fa-bowl-food"></i>
              </div>
              <div class="size-name">{{ size.name }}</div>
              <div class="size-weight">{{ size.weight }}</div>
              <div class="size-price">${{ formatNumber(size.basePrice) }}</div>
              <div v-if="selectedSize && selectedSize.key === size.key" class="selected-check">
                <i class="fas fa-check-circle"></i>
              </div>
            </div>
          </div>
        </div>
        </transition>

        <!-- PASO 3: Salsa → productos reales categoría 7 -->
        <transition name="slide-down">
          <div v-if="selectedSize" class="step-section">
            <h4 class="step-title">
              <span class="step-badge">3</span>
              Elige tu salsa
              <span class="step-included-badge">incluida en el precio</span>
            </h4>

            <div class="salsa-grid">
              <div
                v-for="salsa in salsas"
                :key="salsa.id"
                @click="selectedSalsa = salsa"
                :class="['salsa-card', { selected: selectedSalsa && selectedSalsa.key === salsa.key }]">
                <div class="salsa-icon">🌶️</div>
                <div class="salsa-name">{{ salsa.name }}</div>
                <div v-if="selectedSalsa && selectedSalsa.key === salsa.key" class="selected-check">
                  <i class="fas fa-check-circle"></i>
                </div>
              </div>
            </div>
          </div>
        </transition>

        <!-- PASO 3: Proteína + Bebida (aparece al elegir salsa) -->
        <transition name="slide-down">
          <div v-if="selectedSalsa" class="step-section extras-section">
            <h4 class="step-title">
              <span class="step-badge">4</span>
              Extras opcionales
            </h4>

            <!-- Proteínas -->
            <div class="extras-group">
              <div class="extras-label">
                <i class="fas fa-drumstick-bite"></i>
                Proteína
                <span class="extra-price-tag">+${{ formatNumber(selectedSize ? selectedSize.proteinExtra : 0) }}</span>
              </div>
              <div class="protein-options">
                <div
                  v-for="protein in PROTEINS"
                  :key="protein.key"
                  @click="toggleProtein(protein)"
                  :class="['extra-card', { selected: selectedProtein && selectedProtein.key === protein.key }]">
                  <span class="extra-emoji">{{ protein.emoji }}</span>
                  <span class="extra-name">{{ protein.name }}</span>
                  <span v-if="selectedProtein && selectedProtein.key === protein.key" class="extra-check">
                    <i class="fas fa-check"></i>
                  </span>
                </div>
              </div>
            </div>

            <!-- Bebidas → productos reales categoría 3 -->
            <div class="extras-group">
              <div class="extras-label">
                <i class="fas fa-glass-water"></i>
                Bebida
                <span v-if="selectedBebida" class="extra-price-tag">+${{ formatNumber(selectedSize ? selectedSize.drinkExtra : 0) }}</span>
                <span v-else class="extra-price-tag" style="background:#f0f0f0;color:#888;border-color:#ddd">opcional</span>
              </div>

              <div v-if="bebidas.length === 0" class="no-products-hint">
                <i class="fas fa-exclamation-circle"></i>
                No hay bebidas disponibles (categoría 3)
              </div>

              <div v-else class="bebidas-grid">
                <div
                  v-for="bebida in bebidas"
                  :key="bebida.id"
                  @click="toggleBebida(bebida)"
                  :class="['bebida-card', { selected: selectedBebida && selectedBebida.id === bebida.id }]">
                  <div class="bebida-name">{{ bebida.name }}</div>
                  <div v-if="selectedBebida && selectedBebida.id === bebida.id" class="selected-check">
                    <i class="fas fa-check-circle"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Extras → productos reales categoría 4 -->
            <div v-if="extras.length > 0" class="extras-group">
              <div class="extras-label">
                <i class="fas fa-plus-circle"></i>
                Extras
                <span class="extra-price-tag" style="background:#f0f0f0;color:#888;border-color:#ddd">precio por producto</span>
              </div>
              <div class="extras-cat-grid">
                <div
                  v-for="extra in extras"
                  :key="extra.id"
                  @click="toggleBowlExtra(extra)"
                  :class="['extra-cat-card', { selected: selectedBowlExtras.some(e => e.id === extra.id) }]">
                  <span class="extra-cat-name">{{ extra.name }}</span>
                  <span class="extra-cat-price">+${{ formatNumber(parseInt(extra.price, 10)) }}</span>
                  <span v-if="selectedBowlExtras.some(e => e.id === extra.id)" class="extra-check">
                    <i class="fas fa-check"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </transition>

        <!-- Sugerencia: Salsa Especial (Bowl) -->
        <transition name="slide-down">
          <div v-if="selectedSalsa" class="extras-group protein-salsas-upsell">
            <div class="extras-label">
              <i class="fas fa-magic"></i>
              Sugerencia: Proteína Extra
              <span class="extra-price-tag">desde +$1.000</span>
            </div>
            <div class="extras-cat-grid">
              <div
                v-for="ps in PROTEIN_SALSAS"
                :key="ps.key"
                @click="toggleProteinSalsa('bowl', ps)"
                :class="['extra-cat-card', { selected: selectedBowlProteinSals.some(x => x.key === ps.key) }]">
                <span class="extra-cat-name">{{ ps.emoji }} {{ ps.name }}</span>
                <span class="extra-cat-price">+${{ formatNumber(ps.price) }}</span>
                <span v-if="selectedBowlProteinSals.some(x => x.key === ps.key)" class="extra-check">
                  <i class="fas fa-check"></i>
                </span>
              </div>
            </div>
          </div>
        </transition>

        <!-- RESUMEN del combo -->
        <transition name="slide-down">
          <div v-if="selectedSalsa" class="combo-summary-fagotto">
            <div class="summary-title">
              <i class="fas fa-receipt"></i>
              Resumen de tu combo
            </div>
            <div class="summary-lines">
              <div v-if="selectedPasta" class="summary-line">
                <span>Pasta {{ selectedPasta.name }}</span>
                <span class="text-included">incluida</span>
              </div>
              <div class="summary-line">
                <span>{{ selectedSize.name }}</span>
                <span>${{ formatNumber(selectedSize.basePrice) }}</span>
              </div>
              <div class="summary-line">
                <span>Salsa {{ selectedSalsa.name }}</span>
                <span class="text-included">incluida</span>
              </div>
              <div v-if="selectedProtein" class="summary-line extra">
                <span>+ {{ selectedProtein.name }}</span>
                <span>+${{ formatNumber(selectedSize.proteinExtra) }}</span>
              </div>
              <div v-if="selectedBebida" class="summary-line extra">
                <span>+ {{ selectedBebida.name }}</span>
                <span>+${{ formatNumber(selectedSize.drinkExtra) }}</span>
              </div>
              <div v-for="ex in selectedBowlExtras" :key="'bex-' + ex.id" class="summary-line extra">
                <span>+ {{ ex.name }}</span>
                <span>+${{ formatNumber(parseInt(ex.price, 10)) }}</span>
              </div>
              <div v-for="ps in selectedBowlProteinSals" :key="'bps-' + ps.key" class="summary-line extra">
                <span>+ {{ ps.name }}</span>
                <span>+${{ formatNumber(ps.price) }}</span>
              </div>
              <div class="summary-total">
                <span>TOTAL</span>
                <span>${{ formatNumber(currentPrice) }}</span>
              </div>
            </div>
          </div>
        </transition>

        </div><!-- end bowl mode -->

        <!-- === MODO COMBOS === -->
        <div v-show="activeTab === 'combo'">

          <!-- Elegir tipo de combo -->
          <div class="step-section">
            <h4 class="step-title">
              <span class="step-badge" style="background:linear-gradient(135deg,#8e1a0e,#e74c3c)">✦</span>
              Elige tu combo
            </h4>
            <div class="combos-grid">
              <div
                v-for="combo in COMBOS"
                :key="combo.key"
                @click="selectCombo(combo)"
                :class="['combo-card', { selected: selectedCombo && selectedCombo.key === combo.key }]">
                <div class="combo-emoji">{{ combo.emoji }}</div>
                <div class="combo-name">{{ combo.name }}</div>
                <div class="combo-desc">{{ combo.description }}</div>
                <div class="combo-price">${{ formatNumber(combo.price) }}</div>
                <div v-if="selectedCombo && selectedCombo.key === combo.key" class="selected-check">
                  <i class="fas fa-check-circle"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Pasos dinámicos del combo -->
          <template v-if="selectedCombo">
            <transition
              v-for="(step, i) in selectedCombo.steps"
              :key="step.key"
              name="slide-down">
              <div v-if="comboStepVisible(i)" class="step-section">
                <h4 class="step-title">
                  <span class="step-badge">{{ i + 1 }}</span>
                  {{ step.subtitle }}
                </h4>

                <!-- Tipo pasta -->
                <div v-if="step.type === 'pasta'" class="pasta-grid">
                  <div
                    v-for="pasta in PASTAS"
                    :key="pasta.key"
                    @click="setComboSelection(step.key, pasta)"
                    :class="['pasta-card', { selected: comboSelections[step.key] && comboSelections[step.key].key === pasta.key }]">
                    <div class="pasta-emoji">{{ pasta.emoji }}</div>
                    <div class="pasta-name">{{ pasta.name }}</div>
                    <div v-if="comboSelections[step.key] && comboSelections[step.key].key === pasta.key" class="selected-check">
                      <i class="fas fa-check-circle"></i>
                    </div>
                  </div>
                </div>

                <!-- Salsa -->
                <div v-if="step.type === 'salsa'" class="salsa-grid">
                  <div
                    v-for="salsa in SALSAS_COMUNES"
                    :key="salsa.key"
                    @click="setComboSelection(step.key, salsa)"
                    :class="['salsa-card', { selected: comboSelections[step.key] && comboSelections[step.key].key === salsa.key }]">
                    <div class="salsa-icon">🌶️</div>
                    <div class="salsa-name">{{ salsa.name }}</div>
                    <div v-if="comboSelections[step.key] && comboSelections[step.key].key === salsa.key" class="selected-check">
                      <i class="fas fa-check-circle"></i>
                    </div>
                  </div>
                </div>
              </div>
            </transition>

            <!-- Bebidas (opcionales, aparecen tras completar pasos requeridos) -->
            <transition name="slide-down">
              <div v-if="comboShowBebidas" class="step-section">
                <h4 class="step-title">
                  <span class="step-badge">🥤</span>
                  Bebidas
                  <span class="step-included-badge">incluidas en el combo</span>
                </h4>
                <div
                  v-for="(_, idx) in comboBebidas"
                  :key="'bebida-slot-' + idx"
                  class="bebida-slot">
                  <div v-if="selectedCombo.bebidasCount > 1" class="bebida-slot-label">
                    Bebida {{ idx + 1 }}
                  </div>
                  <div class="bebidas-grid">
                    <div
                      v-for="bebida in bebidas"
                      :key="bebida.id"
                      @click="setComboBebida(idx, bebida)"
                      :class="['bebida-card', { selected: comboBebidas[idx] && comboBebidas[idx].id === bebida.id }]">
                      <div class="bebida-name">{{ bebida.name }}</div>
                      <div v-if="comboBebidas[idx] && comboBebidas[idx].id === bebida.id" class="selected-check">
                        <i class="fas fa-check-circle"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </transition>

            <!-- Extras cat 4 para combo -->
            <transition name="slide-down">
              <div v-if="comboShowBebidas && extras.length > 0" class="step-section">
                <h4 class="step-title">
                  <span class="step-badge">➕</span>
                  Extras opcionales
                  <span class="extra-price-tag" style="margin-left:6px;font-size:0.78rem;background:#fff3cd;color:#856404;border:1px solid #ffc107;border-radius:20px;padding:2px 10px">precio por producto</span>
                </h4>
                <div class="extras-cat-grid">
                  <div
                    v-for="extra in extras"
                    :key="extra.id"
                    @click="toggleComboExtra(extra)"
                    :class="['extra-cat-card', { selected: selectedComboExtras.some(e => e.id === extra.id) }]">
                    <span class="extra-cat-name">{{ extra.name }}</span>
                    <span class="extra-cat-price">+${{ formatNumber(parseInt(extra.price, 10)) }}</span>
                    <span v-if="selectedComboExtras.some(e => e.id === extra.id)" class="extra-check">
                      <i class="fas fa-check"></i>
                    </span>
                  </div>
                </div>
              </div>
            </transition>

            <!-- Sugerencia: Salsa Especial (Combo) -->
            <transition name="slide-down">
              <div v-if="comboRequiredDone" class="extras-group protein-salsas-upsell">
                <div class="extras-label">
                  <i class="fas fa-magic"></i>
                  Sugerencia: Proteína Extra
                  <span class="extra-price-tag">desde +$1.000</span>
                </div>
                <div class="extras-cat-grid">
                  <div
                    v-for="ps in PROTEIN_SALSAS"
                    :key="ps.key"
                    @click="toggleProteinSalsa('combo', ps)"
                    :class="['extra-cat-card', { selected: selectedComboProteinSals.some(x => x.key === ps.key) }]">
                    <span class="extra-cat-name">{{ ps.emoji }} {{ ps.name }}</span>
                    <span class="extra-cat-price">+${{ formatNumber(ps.price) }}</span>
                    <span v-if="selectedComboProteinSals.some(x => x.key === ps.key)" class="extra-check">
                      <i class="fas fa-check"></i>
                    </span>
                  </div>
                </div>
              </div>
            </transition>

            <!-- Resumen combo -->
            <transition name="slide-down">
              <div v-if="comboRequiredDone" class="combo-summary-fagotto">
                <div class="summary-title">
                  <i class="fas fa-receipt"></i>
                  Resumen del combo
                </div>
                <div class="summary-lines">
                  <div class="summary-line">
                    <span>{{ selectedCombo.name }}</span>
                    <span>${{ formatNumber(selectedCombo.price) }}</span>
                  </div>
                  <div v-for="step in selectedCombo.steps" :key="'sum-' + step.key" class="summary-line">
                    <span>{{ step.label }}: {{ comboSelections[step.key] ? comboSelections[step.key].name : '—' }}</span>
                    <span class="text-included">incluido</span>
                  </div>
                  <div v-if="selectedCombo.includeRisotto" class="summary-line">
                    <span>Risotto</span>
                    <span class="text-included">incluido</span>
                  </div>
                  <template v-for="(beb, bidx) in comboBebidas">
                    <div v-if="beb" :key="'sum-beb-' + bidx" class="summary-line">
                      <span>Bebida{{ selectedCombo.bebidasCount > 1 ? ' ' + (bidx + 1) : '' }}: {{ beb.name }}</span>
                      <span class="text-included">incluida</span>
                    </div>
                  </template>
                  <div v-for="ex in selectedComboExtras" :key="'cex-' + ex.id" class="summary-line extra">
                    <span>+ {{ ex.name }}</span>
                    <span>+${{ formatNumber(parseInt(ex.price, 10)) }}</span>
                  </div>
                  <div v-for="ps in selectedComboProteinSals" :key="'cps-' + ps.key" class="summary-line extra">
                    <span>+ {{ ps.name }}</span>
                    <span>+${{ formatNumber(ps.price) }}</span>
                  </div>
                  <div class="summary-total">
                    <span>TOTAL</span>
                    <span>${{ formatNumber(comboPrice) }}</span>
                  </div>
                </div>
              </div>
            </transition>
          </template>

        </div><!-- end combo mode -->

        <!-- === MODO COMBO 2 === -->
        <div v-show="activeTab === 'combo2'">

          <div class="step-section">
            <h4 class="step-title">
              <span class="step-badge" style="background:linear-gradient(135deg,#8e1a0e,#e74c3c)">★</span>
              Elige tu producto
            </h4>
            <div class="combos-grid" style="grid-template-columns:repeat(3,1fr)">
              <div
                v-for="item in COMBO2_ITEMS"
                :key="item.key"
                @click="selectCombo2Item(item)"
                :class="['combo-card', { selected: selectedCombo2Item && selectedCombo2Item.key === item.key }]">
                <div class="combo-emoji">{{ item.emoji }}</div>
                <div class="combo-name">{{ item.name }}</div>
                <div class="combo-desc">{{ item.description }}</div>
                <div class="combo-price" v-if="item.price">${{ formatNumber(item.price) }}</div>
                <div class="combo-price" v-else-if="item.sizes">{{ item.sizes.map(s=>s.label+' $'+formatNumber(s.price)).join(' / ') }}</div>
                <div v-if="selectedCombo2Item && selectedCombo2Item.key === item.key" class="selected-check">
                  <i class="fas fa-check-circle"></i>
                </div>
              </div>
            </div>
          </div>

          <template v-if="selectedCombo2Item">
            <transition
              v-for="(step, i) in selectedCombo2Item.steps"
              :key="step.key"
              name="slide-down">
              <div v-if="combo2StepVisible(i)" class="step-section">
                <h4 class="step-title">
                  <span class="step-badge">{{ i + 1 }}</span>
                  {{ step.subtitle }}
                </h4>

                <!-- Size -->
                <div v-if="step.type === 'size'" class="sizes-grid" style="grid-template-columns:repeat(2,1fr)">
                  <div
                    v-for="s in selectedCombo2Item.sizes" :key="s.key"
                    @click="setCombo2Selection(step.key, s)"
                    :class="['size-card', { selected: combo2Selections[step.key] && combo2Selections[step.key].key === s.key }]">
                    <div class="size-name">{{ s.label }}</div>
                    <div class="size-price">${{ formatNumber(s.price) }}</div>
                    <div v-if="combo2Selections[step.key] && combo2Selections[step.key].key === s.key" class="selected-check">
                      <i class="fas fa-check-circle"></i>
                    </div>
                  </div>
                </div>

                <!-- Salsa hardcoded -->
                <div v-if="step.type === 'salsa_hard'" class="salsa-grid">
                  <div
                    v-for="salsa in SALSAS_COMUNES" :key="salsa.key"
                    @click="setCombo2Selection(step.key, salsa)"
                    :class="['salsa-card', { selected: combo2Selections[step.key] && combo2Selections[step.key].key === salsa.key }]">
                    <div class="salsa-icon">🌶️</div>
                    <div class="salsa-name">{{ salsa.name }}</div>
                    <div v-if="combo2Selections[step.key] && combo2Selections[step.key].key === salsa.key" class="selected-check">
                      <i class="fas fa-check-circle"></i>
                    </div>
                  </div>
                </div>

                <!-- Salsa DB -->
                <div v-if="step.type === 'salsa_db'" class="salsa-grid">
                  <div
                    v-for="salsa in salsasDB" :key="salsa.id"
                    @click="setCombo2Selection(step.key, salsa)"
                    :class="['salsa-card', { selected: combo2Selections[step.key] && combo2Selections[step.key].id === salsa.id }]">
                    <div class="salsa-icon">🌶️</div>
                    <div class="salsa-name">{{ salsa.name }}</div>
                    <div v-if="combo2Selections[step.key] && combo2Selections[step.key].id === salsa.id" class="selected-check">
                      <i class="fas fa-check-circle"></i>
                    </div>
                  </div>
                </div>

                <!-- Proteína Risotto -->
                <div v-if="step.type === 'protein'" class="protein-options">
                  <div
                    v-for="p in RISOTTO_PROTEINS" :key="p.key"
                    @click="setCombo2Selection(step.key, p)"
                    :class="['extra-card', { selected: combo2Selections[step.key] && combo2Selections[step.key].key === p.key }]">
                    <span class="extra-emoji">{{ p.emoji }}</span>
                    <span class="extra-name">{{ p.name }}</span>
                    <span v-if="combo2Selections[step.key] && combo2Selections[step.key].key === p.key" class="extra-check">
                      <i class="fas fa-check"></i>
                    </span>
                  </div>
                </div>
              </div>
            </transition>

            <!-- Bebida opcional -->
            <transition name="slide-down">
              <div v-if="combo2RequiredDone && selectedCombo2Item.hasBebida" class="step-section">
                <h4 class="step-title">
                  <span class="step-badge">🥤</span>
                  Bebida
                  <span class="step-included-badge">incluida en el combo</span>
                </h4>
                <div class="bebidas-grid">
                  <div
                    v-for="bebida in bebidas" :key="bebida.id"
                    @click="combo2Bebida = (combo2Bebida && combo2Bebida.id === bebida.id) ? null : bebida"
                    :class="['bebida-card', { selected: combo2Bebida && combo2Bebida.id === bebida.id }]">
                    <div class="bebida-name">{{ bebida.name }}</div>
                    <div v-if="combo2Bebida && combo2Bebida.id === bebida.id" class="selected-check">
                      <i class="fas fa-check-circle"></i>
                    </div>
                  </div>
                </div>
              </div>
            </transition>

            <!-- Extras cat 4 -->
            <transition name="slide-down">
              <div v-if="combo2RequiredDone && extras.length > 0" class="step-section">
                <h4 class="step-title">
                  <span class="step-badge">➕</span>
                  Extras opcionales
                  <span class="extra-price-tag" style="margin-left:6px;font-size:0.78rem;background:#fff3cd;color:#856404;border:1px solid #ffc107;border-radius:20px;padding:2px 10px">precio por producto</span>
                </h4>
                <div class="extras-cat-grid">
                  <div
                    v-for="extra in extras" :key="extra.id"
                    @click="toggleCombo2Extra(extra)"
                    :class="['extra-cat-card', { selected: combo2Extras.some(e => e.id === extra.id) }]">
                    <span class="extra-cat-name">{{ extra.name }}</span>
                    <span class="extra-cat-price">+${{ formatNumber(parseInt(extra.price, 10)) }}</span>
                    <span v-if="combo2Extras.some(e => e.id === extra.id)" class="extra-check">
                      <i class="fas fa-check"></i>
                    </span>
                  </div>
                </div>
              </div>
            </transition>

            <!-- Sugerencia: Salsa Especial (Combo2) -->
            <transition name="slide-down">
              <div v-if="combo2RequiredDone" class="extras-group protein-salsas-upsell">
                <div class="extras-label">
                  <i class="fas fa-magic"></i>
                  Sugerencia: Proteína Extra
                  <span class="extra-price-tag">desde +$1.000</span>
                </div>
                <div class="extras-cat-grid">
                  <div
                    v-for="ps in PROTEIN_SALSAS"
                    :key="ps.key"
                    @click="toggleProteinSalsa('combo2', ps)"
                    :class="['extra-cat-card', { selected: selectedCombo2ProteinSals.some(x => x.key === ps.key) }]">
                    <span class="extra-cat-name">{{ ps.emoji }} {{ ps.name }}</span>
                    <span class="extra-cat-price">+${{ formatNumber(ps.price) }}</span>
                    <span v-if="selectedCombo2ProteinSals.some(x => x.key === ps.key)" class="extra-check">
                      <i class="fas fa-check"></i>
                    </span>
                  </div>
                </div>
              </div>
            </transition>

            <!-- Resumen combo2 -->
            <transition name="slide-down">
              <div v-if="combo2RequiredDone" class="combo-summary-fagotto">
                <div class="summary-title"><i class="fas fa-receipt"></i> Resumen</div>
                <div class="summary-lines">
                  <div class="summary-line">
                    <span>{{ selectedCombo2Item.name }}{{ combo2Selections.size ? ' ' + combo2Selections.size.label : '' }}</span>
                    <span>${{ formatNumber(combo2Price - combo2Extras.reduce((a,e)=>a+(parseInt(e.price,10)||0),0) - selectedCombo2ProteinSals.reduce((a,p)=>a+p.price,0)) }}</span>
                  </div>
                  <div v-if="selectedCombo2Item.includeCheddar" class="summary-line">
                    <span>🧀 Cheddar</span>
                    <span class="text-included">incluido</span>
                  </div>
                  <div v-for="step in selectedCombo2Item.steps.filter(s=>s.type!=='size')" :key="'c2s-'+step.key" class="summary-line">
                    <span>{{ step.label }}: {{ combo2Selections[step.key] ? combo2Selections[step.key].name : '—' }}</span>
                    <span class="text-included">incluido</span>
                  </div>
                  <div v-if="combo2Bebida" class="summary-line">
                    <span>Bebida: {{ combo2Bebida.name }}</span>
                    <span class="text-included">incluida</span>
                  </div>
                  <div v-for="ex in combo2Extras" :key="'c2ex-'+ex.id" class="summary-line extra">
                    <span>+ {{ ex.name }}</span>
                    <span>+${{ formatNumber(parseInt(ex.price,10)) }}</span>
                  </div>
                  <div v-for="ps in selectedCombo2ProteinSals" :key="'c2ps-' + ps.key" class="summary-line extra">
                    <span>+ {{ ps.name }}</span>
                    <span>+${{ formatNumber(ps.price) }}</span>
                  </div>
                  <div class="summary-total">
                    <span>TOTAL</span>
                    <span>${{ formatNumber(combo2Price) }}</span>
                  </div>
                </div>
              </div>
            </transition>
          </template>

        </div><!-- end combo2 mode -->

        <!-- === MODO CIABATTA === -->
        <div v-show="activeTab === 'ciabatta'">
          <div class="step-section">
            <h4 class="step-title">
              <span class="step-badge" style="background:linear-gradient(135deg,#1a6e2e,#27ae60)">C</span>
              Elige tu Ciabatta
            </h4>
            <p style="font-size:0.82rem;color:#666;margin:-4px 0 12px;">✓ Incluye Bebida Normal o Limonada (Sabores)</p>
            <div class="combos-grid">
              <div
                v-for="cb in CIABATTAS"
                :key="cb.key"
                @click="selectedCiabatta = selectedCiabatta && selectedCiabatta.key === cb.key ? null : cb"
                :class="['combo-card', { selected: selectedCiabatta && selectedCiabatta.key === cb.key }]">
                <div class="combo-emoji">{{ cb.emoji }}</div>
                <div class="combo-name">{{ cb.name }}</div>
                <div class="combo-desc">Bebida Normal o Limonada</div>
                <div class="combo-price">${{ formatNumber(cb.price) }}</div>
                <div v-if="selectedCiabatta && selectedCiabatta.key === cb.key" class="selected-check">
                  <i class="fas fa-check-circle"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Bebida ciabatta -->
          <transition name="slide-down">
            <div v-if="selectedCiabatta" class="step-section">
              <h4 class="step-title">
                <span class="step-badge" style="background:linear-gradient(135deg,#1a6e2e,#27ae60)">🥤</span>
                Elige tu bebida
              </h4>
              <div class="extras-cat-grid">
                <div
                  v-for="beb in bebidas"
                  :key="beb.id"
                  @click="ciabattaBebida = ciabattaBebida && ciabattaBebida.id === beb.id ? null : beb"
                  :class="['extra-cat-card', { selected: ciabattaBebida && ciabattaBebida.id === beb.id }]">
                  <span class="extra-cat-name">{{ beb.name }}</span>
                  <span class="extra-cat-price text-included">incluida</span>
                  <span v-if="ciabattaBebida && ciabattaBebida.id === beb.id" class="extra-check">
                    <i class="fas fa-check"></i>
                  </span>
                </div>
              </div>
            </div>
          </transition>

          <!-- Extra opcional -->
          <transition name="slide-down">
            <div v-if="selectedCiabatta" class="step-section">
              <h4 class="step-title">
                <span class="step-badge" style="background:#e74c3c">+</span>
                ¿Agregar Arancini o Mc N' Chesse?
                <span style="font-size:0.8rem;color:#e74c3c;margin-left:6px;">+$990</span>
              </h4>
              <div class="combos-grid" style="grid-template-columns:repeat(2,1fr)">
                <div
                  v-for="ex in CIABATTA_EXTRAS"
                  :key="ex.key"
                  @click="ciabattaAddExtra = ciabattaAddExtra && ciabattaAddExtra.key === ex.key ? null : ex"
                  :class="['combo-card', { selected: ciabattaAddExtra && ciabattaAddExtra.key === ex.key }]">
                  <div class="combo-emoji">{{ ex.emoji }}</div>
                  <div class="combo-name">{{ ex.name }}</div>
                  <div class="combo-price">+${{ formatNumber(ex.price) }}</div>
                  <div v-if="ciabattaAddExtra && ciabattaAddExtra.key === ex.key" class="selected-check">
                    <i class="fas fa-check-circle"></i>
                  </div>
                </div>
              </div>
            </div>
          </transition>

          <!-- Resumen ciabatta -->
          <transition name="slide-down">
            <div v-if="selectedCiabatta" class="combo-summary-fagotto">
              <div class="summary-title"><i class="fas fa-receipt"></i> Resumen</div>
              <div class="summary-lines">
                <div class="summary-line">
                  <span>{{ selectedCiabatta.name }}</span>
                  <span>${{ formatNumber(selectedCiabatta.price) }}</span>
                </div>
                <div class="summary-line">
                  <span>Bebida: {{ ciabattaBebida ? ciabattaBebida.name : 'Por confirmar' }}</span>
                  <span class="text-included">incluida</span>
                </div>
                <div v-if="ciabattaAddExtra" class="summary-line extra">
                  <span>+ {{ ciabattaAddExtra.name }}</span>
                  <span>+${{ formatNumber(ciabattaAddExtra.price) }}</span>
                </div>
                <div class="summary-total">
                  <span>TOTAL</span>
                  <span>${{ formatNumber(ciabattaPrice) }}</span>
                </div>
              </div>
            </div>
          </transition>
        </div><!-- end ciabatta mode -->

      </div>

      <!-- Footer -->
      <div class="fagotto-footer">
        <div class="footer-hint">
          <i class="fas fa-info-circle"></i>
          <template v-if="activeTab === 'bowl'">
            <span v-if="!selectedPasta">Selecciona tu pasta para comenzar</span>
            <span v-else-if="!selectedSize">Ahora elige el tamaño</span>
            <span v-else-if="!selectedSalsa">Ahora elige tu salsa</span>
            <span v-else>¡Listo! Confirma tu bowl</span>
          </template>
          <template v-else-if="activeTab === 'combo'">
            <span v-if="!selectedCombo">Elige tu combo para comenzar</span>
            <span v-else-if="!comboRequiredDone">Completa las selecciones requeridas</span>
            <span v-else>¡Listo! Confirma tu combo</span>
          </template>
          <template v-else-if="activeTab === 'combo2'">
            <span v-if="!selectedCombo2Item">Elige un producto para comenzar</span>
            <span v-else-if="!combo2RequiredDone">Completa las selecciones requeridas</span>
            <span v-else>¡Listo! Confirma tu pedido</span>
          </template>
          <template v-else>
            <span v-if="!selectedCiabatta">Elige tu ciabatta para comenzar</span>
            <span v-else>¡Listo! Confirma tu Ciabatta</span>
          </template>
        </div>
        <button
          v-if="activeTab === 'bowl' && selectedSalsa"
          @click="confirmarPedido"
          class="btn-confirmar-fagotto">
          <i class="fas fa-plus-circle me-2"></i>
          Agregar — ${{ formatNumber(currentPrice) }}
        </button>
        <button
          v-if="activeTab === 'combo' && comboRequiredDone"
          @click="confirmarCombo"
          class="btn-confirmar-fagotto">
          <i class="fas fa-plus-circle me-2"></i>
          Agregar — ${{ formatNumber(comboPrice) }}
        </button>
        <button
          v-if="activeTab === 'combo2' && combo2RequiredDone"
          @click="confirmarCombo2"
          class="btn-confirmar-fagotto">
          <i class="fas fa-plus-circle me-2"></i>
          Agregar — ${{ formatNumber(combo2Price) }}
        </button>
        <button
          v-if="activeTab === 'ciabatta' && selectedCiabatta"
          @click="confirmarCiabatta"
          class="btn-confirmar-fagotto">
          <i class="fas fa-plus-circle me-2"></i>
          Agregar — ${{ formatNumber(ciabattaPrice) }}
        </button>
      </div>

    </div>
  </div>
</template>

<script>
// ============================================================
// HARDCODED: Tamaños, salsas y precios
// Actualizar precios/salsas aquí cuando sea necesario
// ============================================================
// Salsas disponibles (hardcoded por tamaño)
const SALSAS_COMUNES = [
  { key: 'bolonesa',   name: 'Boloñesa'   },
  { key: 'alfredo',   name: 'Alfredo'    },
  { key: 'champinon', name: 'Champiñón'  },
  { key: 'pesto',     name: 'Pesto'      },
  { key: 'pomodoro',  name: 'Pomodoro'   },
];

const SIZES = [
  {
    key: 'bowl_m',
    name: 'Bowl M',
    weight: '220 gr',
    basePrice: 5990,
    proteinExtra: 1000,
    drinkExtra:  1000,
    salsas: SALSAS_COMUNES,
  },
  {
    key: 'bowl_l',
    name: 'Bowl L',
    weight: '250 gr',
    basePrice: 6990,
    proteinExtra: 1000,
    drinkExtra:  1000,
    salsas: SALSAS_COMUNES,
  },
];

// IDs de categorías en la base de datos
const CAT_BEBIDAS = 3;
const CAT_EXTRAS  = 4;

const PROTEINS = [
  { key: 'pollo',   name: 'Pollo',   emoji: '🍗' },
  { key: 'camaron', name: 'Camarón', emoji: '🦐' },
];

const PASTAS = [
  { key: 'fettuccine', name: 'Fettuccine', emoji: '🍝' },
  { key: 'bigoli',     name: 'Bigoli',     emoji: '🍜' },
  { key: 'noqui',      name: 'Ñoqui',      emoji: '🥟' },
];

const COMBOS = [
  {
    key: 'individual',
    name: 'Combo Individual',
    emoji: '🍝',
    price: 6990,
    description: '1 Pasta Bowl M · 1 Salsa · 1 Bebida',
    bebidasCount: 1,
    steps: [
      { key: 'pasta_1', type: 'pasta', label: 'Pasta',  subtitle: 'Bowl M — elige tu pasta' },
      { key: 'salsa_1', type: 'salsa', label: 'Salsa',  subtitle: 'Elige tu salsa' },
    ],
  },
  {
    key: 'big',
    name: 'Combo Big',
    emoji: '🍲',
    price: 7990,
    description: '1 Pasta Bowl L · 2 Salsas · 1 Bebida',
    bebidasCount: 1,
    steps: [
      { key: 'pasta_1', type: 'pasta', label: 'Pasta',   subtitle: 'Bowl L — elige tu pasta' },
      { key: 'salsa_1', type: 'salsa', label: 'Salsa 1', subtitle: 'Primera salsa' },
      { key: 'salsa_2', type: 'salsa', label: 'Salsa 2', subtitle: 'Segunda salsa' },
    ],
  },
  {
    key: 'para_dos',
    name: 'Combo Para Dos',
    emoji: '👫',
    price: 12990,
    description: '2 Pastas Bowl M · 2 Salsas · 2 Bebidas',
    bebidasCount: 2,
    steps: [
      { key: 'pasta_1', type: 'pasta', label: 'Pasta 1', subtitle: 'Bowl M #1 — elige tu pasta' },
      { key: 'salsa_1', type: 'salsa', label: 'Salsa 1', subtitle: 'Salsa del Bowl #1' },
      { key: 'pasta_2', type: 'pasta', label: 'Pasta 2', subtitle: 'Bowl M #2 — elige tu pasta' },
      { key: 'salsa_2', type: 'salsa', label: 'Salsa 2', subtitle: 'Salsa del Bowl #2' },
    ],
  },
  {
    key: 'para_tres',
    name: 'Combo Para Tres',
    emoji: '👨‍👩‍👦',
    price: 19990,
    description: '2 Pastas a Elección · 2 Salsas · 1 Risotto · 3 Bebidas',
    bebidasCount: 3,
    includeRisotto: true,
    steps: [
      { key: 'pasta_1', type: 'pasta', label: 'Pasta 1', subtitle: 'Primera pasta — elige tipo' },
      { key: 'salsa_1', type: 'salsa', label: 'Salsa 1', subtitle: 'Salsa de la Pasta 1' },
      { key: 'pasta_2', type: 'pasta', label: 'Pasta 2', subtitle: 'Segunda pasta — elige tipo' },
      { key: 'salsa_2', type: 'salsa', label: 'Salsa 2', subtitle: 'Salsa de la Pasta 2' },
    ],
  },
];

const RISOTTO_PROTEINS = [
  { key: 'pollo_cryspy', name: 'Pollo Cryspy', emoji: '🍗' },
  { key: 'mechada',      name: 'Mechada',      emoji: '🥩' },
];

const COMBO2_ITEMS = [
  {
    key: 'noqui_cocido', name: 'Ñoqui Cocido', emoji: '🍲',
    description: '1 Pasta Bowl · 2 Salsas · 1 Bebida',
    hasBebida: true,
    sizes: [{ key: 'm', label: 'M', price: 6990 }, { key: 'l', label: 'L', price: 7990 }],
    steps: [
      { key: 'size',   type: 'size',       label: 'Tamaño', subtitle: 'Elige el tamaño' },
      { key: 'salsa1', type: 'salsa_hard', label: 'Salsa 1', subtitle: 'Primera salsa' },
      { key: 'salsa2', type: 'salsa_hard', label: 'Salsa 2', subtitle: 'Segunda salsa' },
    ],
  },
  {
    key: 'noqui_frito', name: 'Ñoqui Frito', emoji: '🔥',
    description: '1 Pasta Bowl · 2 Salsas · 1 Bebida',
    hasBebida: true,
    sizes: [{ key: 'm', label: 'M', price: 6590 }, { key: 'l', label: 'L', price: 7590 }],
    steps: [
      { key: 'size',   type: 'size',       label: 'Tamaño', subtitle: 'Elige el tamaño' },
      { key: 'salsa1', type: 'salsa_hard', label: 'Salsa 1', subtitle: 'Primera salsa' },
      { key: 'salsa2', type: 'salsa_hard', label: 'Salsa 2', subtitle: 'Segunda salsa' },
    ],
  },
  {
    key: 'risotto', name: 'Risotto', emoji: '🍚',
    description: '1 Vaso Risotto · 1 Proteína · 1 Bebida',
    hasBebida: true,
    price: 6990,
    steps: [
      { key: 'protein', type: 'protein', label: 'Proteína', subtitle: 'Elige tu proteína' },
    ],
  },
  {
    key: 'pop_corn', name: 'Pop Corn Pollo', emoji: '🍿',
    description: 'Cheddar + Salsa a Elección',
    hasBebida: false,
    includeCheddar: true,
    price: 5290,
    steps: [
      { key: 'salsa1', type: 'salsa_db', label: 'Salsa', subtitle: 'Elige tu salsa' },
    ],
  },
  {
    key: 'arancini', name: 'Arancini', emoji: '🟡',
    description: '3x $2.190 · 5x $3.890',
    hasBebida: false,
    includeCheddar: true,
    sizes: [{ key: '3x', label: '3 unidades', price: 2190 }, { key: '5x', label: '5 unidades', price: 3890 }],
    steps: [
      { key: 'size',   type: 'size',     label: 'Cantidad', subtitle: 'Elige la cantidad' },
      { key: 'salsa1', type: 'salsa_db', label: 'Salsa',    subtitle: 'Elige tu salsa' },
    ],
  },
  {
    key: 'mc_chesse', name: "Mc N' Chesse", emoji: '🧀',
    description: '3x $1.990 · 5x $3.590',
    hasBebida: false,
    includeCheddar: true,
    sizes: [{ key: '3x', label: '3 unidades', price: 1990 }, { key: '5x', label: '5 unidades', price: 3590 }],
    steps: [
      { key: 'size',   type: 'size',     label: 'Cantidad', subtitle: 'Elige la cantidad' },
      { key: 'salsa1', type: 'salsa_db', label: 'Salsa',    subtitle: 'Elige tu salsa' },
    ],
  },
];

const CIABATTAS = [
  { key: 'salami',  name: 'Ciabatta Salami',  emoji: '🥪', price: 6990 },
  { key: 'pesto',   name: 'Ciabatta Pesto',   emoji: '🥪', price: 6990 },
  { key: 'alleato', name: 'Ciabatta Alleato', emoji: '🥪', price: 6990 },
];
const CIABATTA_EXTRAS = [
  { key: 'arancini',  name: 'Arancini',       emoji: '🟡', price: 990 },
  { key: 'mc_chesse', name: "Mc N' Chesse",   emoji: '🧀', price: 990 },
];

const PROTEIN_SALSAS = [
  { key: 'pollo_cryspy',  name: 'Pollo Cryspy',       emoji: '🍗', price: 1990 },
  { key: 'mechada',       name: 'Mechada',             emoji: '🥩', price: 1990 },
  { key: 'pollo_mostaza', name: 'Salsa Pollo Mostaza', emoji: '🟡', price: 1000 },
  { key: 'camaron',       name: 'Salsa Camarón',       emoji: '🦐', price: 1000 },
];

export default {
  name: 'MenuFagotto',

  props: {
    allProducts: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      isVisible: false,
      modalKey: 0,
      SIZES,
      PROTEINS,
      PASTAS,
      COMBOS,
      SALSAS_COMUNES,
      COMBO2_ITEMS,
      RISOTTO_PROTEINS,
      PROTEIN_SALSAS,
      CIABATTAS,
      CIABATTA_EXTRAS,
      // Bowl
      selectedPasta:   null,
      selectedSize:    null,
      selectedSalsa:   null,
      selectedProtein: null,
      selectedBebida:  null,
      // Tabs & combos
      activeTab:          'bowl',
      selectedCombo:      null,
      comboSelections:    {},
      comboBebidas:       [],
      // Extras (cat 4)
      selectedBowlExtras:  [],
      selectedComboExtras: [],
      // Combo 2
      selectedCombo2Item: null,
      combo2Selections:   {},
      combo2Bebida:       null,
      combo2Extras:       [],
      // Ciabatta
      selectedCiabatta:   null,
      ciabattaAddExtra:   null,
      ciabattaBebida:     null,
      // Salsa especial sugerida (+$1.000 c/u)
      selectedBowlProteinSals:  [],
      selectedComboProteinSals: [],
      selectedCombo2ProteinSals: [],
    };
  },

  computed: {
    salsas() {
      if (!this.selectedSize) return [];
      return this.selectedSize.salsas || [];
    },

    bebidas() {
      if (!this.allProducts || !this.allProducts.length) return [];
      return this.allProducts.filter(p => p.category == CAT_BEBIDAS);
    },

    extras() {
      if (!this.allProducts || !this.allProducts.length) return [];
      return this.allProducts.filter(p => p.category == CAT_EXTRAS);
    },

    salsasDB() {
      if (!this.allProducts || !this.allProducts.length) return [];
      return this.allProducts.filter(p => {
        if (p.category != 7) return false;
        const n = p.name.toLowerCase();
        return !n.includes('pollo') && !n.includes('camar');
      });
    },

    // ---------- COMBO 2 ----------
    combo2Price() {
      if (!this.selectedCombo2Item) return 0;
      const item = this.selectedCombo2Item;
      let price = item.price || 0;
      if (item.sizes && this.combo2Selections.size) price = this.combo2Selections.size.price;
      this.combo2Extras.forEach(e => { price += parseInt(e.price, 10) || 0; });
      this.selectedCombo2ProteinSals.forEach(p => { price += p.price; });
      return price;
    },
    // ---------- CIABATTA ----------
    ciabattaPrice() {
      if (!this.selectedCiabatta) return 0;
      let price = this.selectedCiabatta.price;
      if (this.ciabattaAddExtra) price += this.ciabattaAddExtra.price;
      return price;
    },

    combo2RequiredDone() {
      if (!this.selectedCombo2Item) return false;
      return this.selectedCombo2Item.steps.every(s => !!this.combo2Selections[s.key]);
    },

    currentPrice() {
      if (!this.selectedSize) return 0;
      let price = this.selectedSize.basePrice;
      if (this.selectedProtein) price += this.selectedSize.proteinExtra;
      if (this.selectedBebida)  price += this.selectedSize.drinkExtra;
      this.selectedBowlExtras.forEach(e => { price += parseInt(e.price, 10) || 0; });
      this.selectedBowlProteinSals.forEach(p => { price += p.price; });
      return price;
    },

    comboName() {
      if (!this.selectedPasta || !this.selectedSize || !this.selectedSalsa) return '';
      let name = `${this.selectedPasta.name} ${this.selectedSize.name} ${this.selectedSalsa.name}`;
      if (this.selectedProtein) name += ` + ${this.selectedProtein.name}`;
      if (this.selectedBebida)  name += ` + ${this.selectedBebida.name}`;
      this.selectedBowlExtras.forEach(e => { name += ` + ${e.name}`; });
      this.selectedBowlProteinSals.forEach(p => { name += ` + ${p.name}`; });
      return name;
    },

    // ---------- COMBOS ----------
    comboPrice() {
      if (!this.selectedCombo) return 0;
      let price = this.selectedCombo.price;
      this.selectedComboExtras.forEach(e => { price += parseInt(e.price, 10) || 0; });
      this.selectedComboProteinSals.forEach(p => { price += p.price; });
      return price;
    },

    comboRequiredDone() {
      if (!this.selectedCombo) return false;
      return this.selectedCombo.steps.every(step => !!this.comboSelections[step.key]);
    },

    comboShowBebidas() {
      return this.comboRequiredDone;
    },
  },

  methods: {
    openModal() {
      console.log('⭐ [MENU-FAGOTTO] Abriendo modal | bebidas:', this.bebidas.length);
      this.modalKey++;
      this.resetModal();
      this.isVisible = true;
    },

    // ---------- COMBOS ----------
    switchTab(tab) {
      this.activeTab = tab;
    },

    selectCombo(combo) {
      this.selectedCombo    = combo;
      this.comboSelections  = {};
      this.comboBebidas     = new Array(combo.bebidasCount).fill(null);
      this.selectedComboExtras = [];
    },

    comboStepVisible(index) {
      if (!this.selectedCombo) return false;
      if (index === 0) return true;
      const steps = this.selectedCombo.steps;
      for (let i = 0; i < index; i++) {
        if (!this.comboSelections[steps[i].key]) return false;
      }
      return true;
    },

    setComboSelection(key, value) {
      const prev = this.comboSelections[key];
      const isSame = prev && (prev.key === value.key || prev.id === value.id);
      if (isSame) {
        this.$set(this.comboSelections, key, null);
        // Limpiar todos los pasos siguientes
        const steps = this.selectedCombo.steps;
        const idx = steps.findIndex(s => s.key === key);
        for (let i = idx + 1; i < steps.length; i++) {
          this.$set(this.comboSelections, steps[i].key, null);
        }
        this.comboBebidas = new Array(this.selectedCombo.bebidasCount).fill(null);
      } else {
        this.$set(this.comboSelections, key, value);
      }
    },

    setComboBebida(index, bebida) {
      const arr = [...this.comboBebidas];
      arr[index] = (arr[index] && arr[index].id === bebida.id) ? null : bebida;
      this.comboBebidas = arr;
    },

    confirmarCombo() {
      if (!this.comboRequiredDone) return;
      const s = this.comboSelections;
      const bebidasNombres = this.comboBebidas.filter(Boolean).map(b => b.name).join(', ');
      let name = this.selectedCombo.name;
      if (s.pasta_1) name += ` ${s.pasta_1.name}`;
      if (s.salsa_1) name += ` ${s.salsa_1.name}`;
      if (s.salsa_2) name += `+${s.salsa_2.name}`;
      if (s.pasta_2) name += ` / ${s.pasta_2.name} ${s.salsa_2 ? s.salsa_2.name : ''}`;
      if (bebidasNombres) name += ` + ${bebidasNombres}`;
      this.selectedComboProteinSals.forEach(p => { name += ` + ${p.name}`; });

      const comboData = {
        id: `fagotto_combo_${Date.now()}`,
        name,
        price: this.comboPrice,
        promo_price: null,
        quantity: 1,
        prices: [{ precio: this.comboPrice }],
        cecina: false,
        ganancia: 0,
        is_menu_fagotto: true,
        fagotto_details: {
          combo_key:       this.selectedCombo.key,
          combo_name:      this.selectedCombo.name,
          include_risotto: !!this.selectedCombo.includeRisotto,
          ...s,
          bebidas: this.comboBebidas.filter(Boolean).map(b => ({ id: b.id, name: b.name })),
          extras:  this.selectedComboExtras.map(e => ({ id: e.id, name: e.name, price: parseInt(e.price, 10) || 0 })),
          protein_salsas: this.selectedComboProteinSals.map(p => ({ key: p.key, name: p.name, price: 1000 })),
        },
      };

      console.log('⭐ [MENU-FAGOTTO] Emitiendo combo:', comboData);
      this.$emit('addMenuFagotto', comboData);
      setTimeout(() => this.closeModal(), 600);
    },
    // ---------- FIN COMBOS ----------

    selectPasta(pasta) {
      if (this.selectedPasta && this.selectedPasta.key === pasta.key) {
        this.selectedPasta   = null;
        this.selectedSize    = null;
        this.selectedSalsa   = null;
        this.selectedProtein = null;
        this.selectedBebida  = null;
      } else {
        this.selectedPasta = pasta;
      }
    },

    closeModal() {
      this.isVisible = false;
    },

    resetModal() {
      this.selectedPasta       = null;
      this.selectedSize        = null;
      this.selectedSalsa       = null;
      this.selectedProtein     = null;
      this.selectedBebida      = null;
      this.selectedBowlExtras  = [];
      // combo
      this.selectedCombo       = null;
      this.comboSelections     = {};
      this.comboBebidas        = [];
      this.selectedComboExtras = [];
      // combo2
      this.selectedCombo2Item  = null;
      this.combo2Selections    = {};
      this.combo2Bebida        = null;
      this.combo2Extras        = [];
      // protein salsas upsell
      this.selectedBowlProteinSals   = [];
      this.selectedComboProteinSals  = [];
      this.selectedCombo2ProteinSals = [];
      // ciabatta
      this.selectedCiabatta = null;
      this.ciabattaAddExtra = null;
      this.ciabattaBebida   = null;
    },

    selectSize(size) {
      if (this.selectedSize && this.selectedSize.key === size.key) {
        this.selectedSize    = null;
        this.selectedSalsa   = null;
        this.selectedProtein = null;
        this.selectedBebida  = null;
      } else {
        this.selectedSize    = size;
        this.selectedSalsa   = null;
        this.selectedProtein = null;
        this.selectedBebida  = null;
      }
    },

    toggleProtein(protein) {
      this.selectedProtein = (this.selectedProtein && this.selectedProtein.key === protein.key)
        ? null : protein;
    },

    toggleBebida(bebida) {
      this.selectedBebida = (this.selectedBebida && this.selectedBebida.id === bebida.id)
        ? null : bebida;
    },

    toggleBowlExtra(extra) {
      const idx = this.selectedBowlExtras.findIndex(e => e.id === extra.id);
      if (idx >= 0) this.selectedBowlExtras.splice(idx, 1);
      else this.selectedBowlExtras.push(extra);
    },

    toggleComboExtra(extra) {
      const idx = this.selectedComboExtras.findIndex(e => e.id === extra.id);
      if (idx >= 0) this.selectedComboExtras.splice(idx, 1);
      else this.selectedComboExtras.push(extra);
    },

    // ---------- COMBO 2 ----------
    selectCombo2Item(item) {
      this.selectedCombo2Item = item;
      this.combo2Selections   = {};
      this.combo2Bebida       = null;
      this.combo2Extras       = [];
    },

    combo2StepVisible(index) {
      if (!this.selectedCombo2Item) return false;
      if (index === 0) return true;
      const steps = this.selectedCombo2Item.steps;
      for (let i = 0; i < index; i++) {
        if (!this.combo2Selections[steps[i].key]) return false;
      }
      return true;
    },

    setCombo2Selection(key, value) {
      const prev = this.combo2Selections[key];
      const isSame = prev && (prev.key === value.key || prev.id === value.id);
      if (isSame) {
        this.$set(this.combo2Selections, key, null);
        const steps = this.selectedCombo2Item.steps;
        const idx   = steps.findIndex(s => s.key === key);
        for (let i = idx + 1; i < steps.length; i++) {
          this.$set(this.combo2Selections, steps[i].key, null);
        }
        this.combo2Bebida = null;
      } else {
        this.$set(this.combo2Selections, key, value);
      }
    },

    toggleCombo2Extra(extra) {
      const idx = this.combo2Extras.findIndex(e => e.id === extra.id);
      if (idx >= 0) this.combo2Extras.splice(idx, 1);
      else this.combo2Extras.push(extra);
    },

    toggleProteinSalsa(mode, ps) {
      const arr = mode === 'bowl' ? 'selectedBowlProteinSals'
                : mode === 'combo' ? 'selectedComboProteinSals'
                : 'selectedCombo2ProteinSals';
      const idx = this[arr].findIndex(x => x.key === ps.key);
      if (idx >= 0) this[arr].splice(idx, 1);
      else this[arr].push(ps);
    },

    confirmarCombo2() {
      if (!this.combo2RequiredDone) return;
      const s  = this.combo2Selections;
      const item = this.selectedCombo2Item;
      let name = item.name;
      if (s.size)    name += ` ${s.size.label}`;
      if (s.salsa1)  name += ` ${s.salsa1.name}`;
      if (s.salsa2)  name += `+${s.salsa2.name}`;
      if (s.protein) name += ` ${s.protein.name}`;
      if (this.combo2Bebida) name += ` + ${this.combo2Bebida.name}`;
      this.combo2Extras.forEach(e => { name += ` + ${e.name}`; });
      this.selectedCombo2ProteinSals.forEach(p => { name += ` + ${p.name}`; });

      const comboData = {
        id: `fagotto_combo2_${Date.now()}`,
        name,
        price: this.combo2Price,
        promo_price: null,
        quantity: 1,
        prices: [{ precio: this.combo2Price }],
        cecina: false,
        ganancia: 0,
        is_menu_fagotto: true,
        fagotto_details: {
          tipo: 'combo2',
          item_key:  item.key,
          item_name: item.name,
          ...Object.fromEntries(Object.entries(s).map(([k,v]) => [k, v ? v.name : null])),
          bebida: this.combo2Bebida ? this.combo2Bebida.name : null,
          extras: this.combo2Extras.map(e => ({ id: e.id, name: e.name, price: parseInt(e.price, 10) || 0 })),
          protein_salsas: this.selectedCombo2ProteinSals.map(p => ({ key: p.key, name: p.name, price: 1000 })),
        },
      };
      console.log('⭐ [MENU-FAGOTTO] Combo2:', comboData);
      this.$emit('addMenuFagotto', comboData);
      setTimeout(() => this.closeModal(), 600);
    },
    // ---------- FIN COMBO 2 ----------

    confirmarCiabatta() {
      if (!this.selectedCiabatta) return;
      let name = this.selectedCiabatta.name;
      if (this.ciabattaBebida) name += ` + ${this.ciabattaBebida.name}`;
      if (this.ciabattaAddExtra) name += ` + ${this.ciabattaAddExtra.name}`;
      const comboData = {
        id: `fagotto_ciabatta_${Date.now()}`,
        name,
        price: this.ciabattaPrice,
        promo_price: null,
        quantity: 1,
        prices: [{ precio: this.ciabattaPrice }],
        cecina: false,
        ganancia: 0,
        is_menu_fagotto: true,
        fagotto_details: {
          tipo:     'ciabatta',
          ciabatta: this.selectedCiabatta.key,
          bebida_id:   this.ciabattaBebida ? this.ciabattaBebida.id   : null,
          bebida_name: this.ciabattaBebida ? this.ciabattaBebida.name : null,
          extra:    this.ciabattaAddExtra ? { key: this.ciabattaAddExtra.key, name: this.ciabattaAddExtra.name, price: 990 } : null,
        },
      };
      console.log('⭐ [MENU-FAGOTTO] Ciabatta:', comboData);
      this.$emit('addMenuFagotto', comboData);
      setTimeout(() => this.closeModal(), 600);
    },
    // ---------- FIN CIABATTA ----------

    confirmarPedido() {
      if (!this.selectedPasta || !this.selectedSize || !this.selectedSalsa) {
        if (this.$parent && this.$parent.$awn) {
          this.$parent.$awn.warning('Selecciona pasta, tamaño y salsa para continuar');
        }
        return;
      }

      const comboData = {
        id: `fagotto_menu_${Date.now()}`,
        name: this.comboName,
        price: this.currentPrice,
        promo_price: null,
        quantity: 1,
        prices: [{ precio: this.currentPrice }],
        cecina: false,
        ganancia: 0,
        is_menu_fagotto: true,
        fagotto_details: {
          pasta_key:    this.selectedPasta.key,
          pasta_name:   this.selectedPasta.name,
          size_key:     this.selectedSize.key,
          size_name:    this.selectedSize.name,
          size_weight:  this.selectedSize.weight,
          salsa_key:    this.selectedSalsa.key,
          salsa_name:   this.selectedSalsa.name,
          protein_key:  this.selectedProtein ? this.selectedProtein.key : null,
          protein_name: this.selectedProtein ? this.selectedProtein.name : null,
          bebida_id:    this.selectedBebida ? this.selectedBebida.id : null,
          bebida_name:  this.selectedBebida ? this.selectedBebida.name : null,
          bebida_price: this.selectedBebida ? this.selectedSize.drinkExtra : 0,
          extras:         this.selectedBowlExtras.map(e => ({ id: e.id, name: e.name, price: parseInt(e.price, 10) || 0 })),
          protein_salsas: this.selectedBowlProteinSals.map(p => ({ key: p.key, name: p.name, price: 1000 })),
        },
      };

      console.log('⭐ [MENU-FAGOTTO] Emitiendo combo:', comboData);
      this.$emit('addMenuFagotto', comboData);

      if (this.$parent && this.$parent.$awn) {
        this.$parent.$awn.success(`${this.comboName} agregado`, {
          labels: { success: '¡COMBO FAGOTTO AGREGADO!' }
        });
      }

      setTimeout(() => this.closeModal(), 600);
    },

    formatNumber(num) {
      if (!num && num !== 0) return '0';
      return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    },
  },
};
</script>

<style scoped>
/* ===================== OVERLAY ===================== */
.fagotto-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10001;
  padding: 20px;
}

/* ===================== MODAL ===================== */
.fagotto-modal {
  background: #fff;
  border-radius: 20px;
  width: 100%;
  max-width: 680px;
  max-height: 92vh;
  box-shadow: 0 25px 70px rgba(0,0,0,0.35);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  animation: fagottoSlideIn 0.3s ease-out;
}

@keyframes fagottoSlideIn {
  from { opacity: 0; transform: translateY(-25px) scale(0.98); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* ===================== HEADER ===================== */
.fagotto-header {
  background: linear-gradient(135deg, #8e1a0e 0%, #c0392b 60%, #e74c3c 100%);
  padding: 22px 28px 0 28px;
  color: #fff;
  flex-shrink: 0;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.header-logo {
  width: 50px;
  height: 50px;
  background: rgba(255,255,255,0.2);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  animation: star-spin 4s linear infinite;
}

@keyframes star-spin {
  0%   { transform: rotate(0deg) scale(1); }
  50%  { transform: rotate(180deg) scale(1.15); }
  100% { transform: rotate(360deg) scale(1); }
}

.header-title {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: -0.5px;
}

.header-subtitle {
  margin: 3px 0 0;
  font-size: 0.85rem;
  opacity: 0.85;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-price-badge {
  background: rgba(255,255,255,0.25);
  border: 2px solid rgba(255,255,255,0.5);
  border-radius: 25px;
  padding: 6px 16px;
  font-size: 1.1rem;
  font-weight: 800;
}

.btn-close-fagotto {
  background: rgba(255,255,255,0.2);
  border: none;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  color: #fff;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}

.btn-close-fagotto:hover {
  background: rgba(255,255,255,0.35);
  transform: rotate(90deg);
}

/* Barra de progreso */
.progress-bar-container {
  display: flex;
  align-items: center;
  padding: 0 10px 16px;
  gap: 0;
}

.progress-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  opacity: 0.45;
  transition: opacity 0.3s;
}

.progress-step.active {
  opacity: 1;
}

.step-num {
  width: 26px;
  height: 26px;
  background: rgba(255,255,255,0.25);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 700;
}

.progress-step.done .step-num {
  background: rgba(255,255,255,0.9);
  color: #c0392b;
}

.step-label {
  font-size: 0.7rem;
  font-weight: 600;
  white-space: nowrap;
}

.progress-line {
  flex: 1;
  height: 2px;
  background: rgba(255,255,255,0.2);
  margin: 0 6px;
  margin-bottom: 20px;
  transition: background 0.3s;
}

.progress-line.active {
  background: rgba(255,255,255,0.7);
}

/* ===================== TABS ===================== */
.fagotto-tabs {
  display: flex;
  gap: 0;
  margin-bottom: 14px;
  background: rgba(0,0,0,0.18);
  border-radius: 10px;
  padding: 3px;
}

.fagotto-tab {
  flex: 1;
  background: transparent;
  border: none;
  color: rgba(255,255,255,0.6);
  font-size: 0.88rem;
  font-weight: 700;
  padding: 7px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.fagotto-tab.active {
  background: rgba(255,255,255,0.95);
  color: #c0392b;
}

/* ---- Combos grid ---- */
.combos-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.combo-card {
  border: 2px solid #e8e8e8;
  border-radius: 14px;
  padding: 16px 12px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
  background: #fafafa;
}

.combo-card:hover {
  border-color: #e74c3c;
  background: #fff5f5;
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(192,57,43,0.15);
}

.combo-card.selected {
  border-color: #c0392b;
  background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%);
  box-shadow: 0 6px 20px rgba(192,57,43,0.25);
}

.combo-emoji {
  font-size: 2rem;
  margin-bottom: 6px;
}

.combo-name {
  font-weight: 800;
  font-size: 0.95rem;
  color: #2d2d2d;
}

.combo-desc {
  font-size: 0.71rem;
  color: #888;
  line-height: 1.4;
  margin: 4px 0 6px;
}

.combo-price {
  font-weight: 800;
  font-size: 1rem;
  color: #c0392b;
}

/* Bebida slots para combo */
.bebida-slot {
  margin-bottom: 14px;
}

.bebida-slot-label {
  font-size: 0.82rem;
  font-weight: 700;
  color: #666;
  margin-bottom: 8px;
}

/* ===================== BODY ===================== */
.fagotto-body {
  padding: 24px 28px;
  overflow-y: auto;
  flex: 1;
}

.step-section {
  margin-bottom: 24px;
}

.step-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 1rem;
  font-weight: 700;
  color: #2d2d2d;
  margin-bottom: 14px;
}

.step-badge {
  width: 26px;
  height: 26px;
  background: linear-gradient(135deg, #c0392b, #e74c3c);
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 800;
  flex-shrink: 0;
}

.step-included-badge {
  font-size: 0.72rem;
  font-weight: 600;
  color: #27ae60;
  background: #eafaf1;
  border: 1px solid #a9dfbf;
  border-radius: 20px;
  padding: 2px 10px;
  margin-left: 4px;
}

/* ---- Tipo de pasta ---- */
.pasta-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
}

.pasta-card {
  border: 2px solid #e8e8e8;
  border-radius: 14px;
  padding: 22px 12px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
  background: #fafafa;
}

.pasta-card:hover {
  border-color: #e74c3c;
  background: #fff5f5;
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(192,57,43,0.15);
}

.pasta-card.selected {
  border-color: #c0392b;
  background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%);
  box-shadow: 0 6px 20px rgba(192,57,43,0.25);
}

.pasta-emoji {
  font-size: 2.2rem;
  margin-bottom: 8px;
}

.pasta-name {
  font-weight: 800;
  font-size: 1.1rem;
  color: #2d2d2d;
}

/* ---- Tamaños ---- */
.sizes-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

.size-card {
  border: 2px solid #e8e8e8;
  border-radius: 14px;
  padding: 16px 12px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
  background: #fafafa;
}

.size-card:hover {
  border-color: #e74c3c;
  background: #fff5f5;
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(192,57,43,0.15);
}

.size-card.selected {
  border-color: #c0392b;
  background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%);
  box-shadow: 0 6px 20px rgba(192,57,43,0.25);
}

.size-icon {
  font-size: 1.8rem;
  margin-bottom: 6px;
  color: #c0392b;
}

.size-name {
  font-weight: 800;
  font-size: 1rem;
  color: #2d2d2d;
}

.size-weight {
  font-size: 0.75rem;
  color: #888;
  margin: 2px 0 6px;
}

.size-price {
  font-size: 1rem;
  font-weight: 700;
  color: #c0392b;
}

/* ---- Salsas ---- */
.salsa-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.salsa-card {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border: 2px solid #e8e8e8;
  border-radius: 30px;
  cursor: pointer;
  transition: all 0.2s;
  background: #fafafa;
  position: relative;
}

.salsa-card:hover {
  border-color: #e74c3c;
  background: #fff5f5;
}

.salsa-card.selected {
  border-color: #c0392b;
  background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%);
  font-weight: 700;
}

.salsa-icon {
  font-size: 1.2rem;
}

.salsa-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #2d2d2d;
}

/* ---- Extras cat 4 ---- */
.extras-cat-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.extra-cat-card {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  border: 2px solid #e8e8e8;
  border-radius: 30px;
  cursor: pointer;
  background: #fafafa;
  transition: all 0.2s;
}

.extra-cat-card:hover {
  border-color: #e74c3c;
  background: #fff5f5;
}

.extra-cat-card.selected {
  border-color: #c0392b;
  background: #ffeaea;
  font-weight: 700;
}

.extra-cat-name {
  font-size: 0.88rem;
  color: #2d2d2d;
}

.extra-cat-price {
  font-size: 0.8rem;
  font-weight: 700;
  color: #c0392b;
}

/* ---- Extras ---- */
.extras-section {
  background: #f8f9fa;
  border-radius: 14px;
  padding: 18px;
}

.extras-group {
  margin-bottom: 16px;
}

.extras-group:last-child {
  margin-bottom: 0;
}

.protein-salsas-upsell {
  background: linear-gradient(135deg, #fff8e7, #fff3cd);
  border: 1.5px solid #ffc107;
  border-radius: 12px;
  padding: 12px 14px;
}

.protein-salsas-upsell .extras-label {
  color: #856404;
}

.protein-salsas-upsell .extra-price-tag {
  background: #ffc107;
  color: #000;
  border-color: #e0a800;
}

.extras-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.88rem;
  font-weight: 700;
  color: #555;
  margin-bottom: 10px;
}

.extra-price-tag {
  margin-left: 6px;
  font-size: 0.78rem;
  background: #fff3cd;
  color: #856404;
  border: 1px solid #ffc107;
  border-radius: 20px;
  padding: 2px 10px;
}

.protein-options {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.extra-card {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border: 2px solid #e8e8e8;
  border-radius: 30px;
  cursor: pointer;
  background: #fff;
  transition: all 0.2s;
}

.extra-card:hover {
  border-color: #e74c3c;
}

.extra-card.selected {
  border-color: #c0392b;
  background: #ffeaea;
  font-weight: 700;
}

.extra-emoji {
  font-size: 1.1rem;
}

.extra-name {
  font-size: 0.9rem;
  color: #2d2d2d;
}

.extra-check {
  color: #c0392b;
  font-size: 0.9rem;
}

.btn-drink-toggle {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border: 2px solid #e8e8e8;
  border-radius: 30px;
  background: #fff;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  color: #555;
}

.btn-drink-toggle:hover {
  border-color: #e74c3c;
}

.btn-drink-toggle.active {
  border-color: #c0392b;
  background: #ffeaea;
  color: #c0392b;
}

/* ---- Bebidas grid ---- */
.bebidas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
  gap: 10px;
}

.bebida-card {
  border: 2px solid #e8e8e8;
  border-radius: 12px;
  padding: 12px 10px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  background: #fff;
  position: relative;
}

.bebida-card:hover {
  border-color: #e74c3c;
  background: #fff5f5;
  transform: translateY(-2px);
}

.bebida-card.selected {
  border-color: #c0392b;
  background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%);
  box-shadow: 0 4px 14px rgba(192,57,43,0.2);
}

.bebida-name { font-size: 0.82rem; font-weight: 600; color: #2d2d2d; margin-bottom: 4px; line-height: 1.3; }
.bebida-price { font-size: 0.92rem; font-weight: 700; color: #c0392b; }

/* ---- No products hint ---- */
.no-products-hint {
  font-size: 0.85rem;
  color: #999;
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 14px;
  background: #f9f9f9;
  border-radius: 10px;
}

/* ---- Selected check ---- */
.selected-check {
  position: absolute;
  top: 8px;
  right: 8px;
  color: #c0392b;
  font-size: 1rem;
}

/* ---- Summary ---- */
.combo-summary-fagotto {
  background: linear-gradient(135deg, #fff9f9 0%, #fff5f5 100%);
  border: 2px solid #f5c6c6;
  border-radius: 14px;
  padding: 18px;
}

.summary-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  color: #c0392b;
  margin-bottom: 12px;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.summary-lines {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.summary-line {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  color: #444;
}

.summary-line.extra {
  color: #c0392b;
  font-weight: 600;
}

.text-included {
  color: #27ae60;
  font-weight: 600;
  font-size: 0.82rem;
}

.summary-total {
  display: flex;
  justify-content: space-between;
  font-size: 1.15rem;
  font-weight: 800;
  color: #c0392b;
  border-top: 2px solid #f5c6c6;
  padding-top: 10px;
  margin-top: 4px;
}

/* ===================== FOOTER ===================== */
.fagotto-footer {
  border-top: 1px solid #f0f0f0;
  padding: 16px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
  background: #fff;
}

.footer-hint {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  color: #888;
}

.btn-confirmar-fagotto {
  background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
  color: #fff;
  border: none;
  border-radius: 30px;
  padding: 13px 28px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 15px rgba(192,57,43,0.4);
}

.btn-confirmar-fagotto:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(192,57,43,0.5);
}

.btn-confirmar-fagotto:active {
  transform: translateY(0);
}

/* ===================== TRANSITIONS ===================== */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}

.slide-down-enter,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-12px);
}
</style>
