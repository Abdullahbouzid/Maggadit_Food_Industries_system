

<nav id="sidebar" class="sidebar js-sidebar">
			<div class="sidebar-content js-simplebar">
				<a class="sidebar-brand" href="{{ route('dashboard') }}">
                    <img src="{{ asset('img/logo/logo.png') }}" alt="Logo" style="width: 50%;  justify-content: center; justify-items: center; display: block; margin-left: auto; margin-right: auto;">
             </a>


				<ul class="sidebar-nav">
                    <li class="sidebar-item active">
						<a class="sidebar-link" href="{{ route('dashboard') }}">
                        <i class="align-middle" data-feather="sliders"></i> <span class="align-middle">Dashboard</span>
                        </a>
					</li>

				<li class="sidebar-header">
                        Data Definitions
                    </li>

                  <li class="sidebar-item">
                    <a data-bs-toggle="collapse" href="#dataDefinitionsMenu" class="sidebar-link collapsed">
                        <i class="align-middle" data-feather="database"></i> 
                        <span class="align-middle">Data Definitions</span>
                    </a>

                    <ul id="dataDefinitionsMenu" class="sidebar-dropdown list-unstyled collapse">
                        <!-- master data definitions with submenu -->
                        <li class="sidebar-item">
                            <a data-bs-toggle="collapse" href="#masterSubmenu" class="sidebar-link collapsed">
                                master data definitions
                            </a>
                            <ul id="masterSubmenu" class="sidebar-dropdown list-unstyled collapse">
                               <li class="sidebar-header">
                                    General
                                </li>

                                
                                <li class="sidebar-item"><a class="sidebar-link" href="{{ route('organizations.index') }}">Organizations </a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">Vendors</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">Pack Statuses </a></li>

                                <li class="sidebar-item"><a class="sidebar-link" href="#">Competitors  </a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#"> Visit Types </a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#"> Fields Group </a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#"> Geographical Location Hierarchy </a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#"> Vehicle Type  </a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#"> Sales Order Type </a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#"> Stock Statuses </a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">  Item Model</a></li>

                                <li class="sidebar-header">
                                    Finance
                                </li>
                               
                                <li class="sidebar-item"><a class="sidebar-link" href="#"> Currencies</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#"> Currency Rates</a></li>                                
                                <li class="sidebar-item"><a class="sidebar-link" href="#"> Banks</a></li>                                
                                <li class="sidebar-item"><a class="sidebar-link" href="#"> Branches</a></li>                                
                                <li class="sidebar-item"><a class="sidebar-link" href="#"> Tax Type</a></li>   
                               
                                
                                  <li class="sidebar-header">
                                    Reasons Definition
                                </li>
                              
                                <li class="sidebar-item"><a class="sidebar-link" href="#"> Reasons</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#"> Reason Groups</a></li>

                            </ul>
                        </li>

                        <!-- staff data definitions with submenu -->
                        <li class="sidebar-item">
                            <a data-bs-toggle="collapse" href="#staffSubmenu" class="sidebar-link collapsed">
                                staff data definitions
                            </a>
                            <ul id="staffSubmenu" class="sidebar-dropdown list-unstyled collapse">
                                <li class="sidebar-item"><a class="sidebar-link" href="#">personal information</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">employment details</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">attendance schema</a></li>
                            </ul>
                        </li>

                        <!-- mahachin data definitions with submenu -->
                        <li class="sidebar-item">
                            <a data-bs-toggle="collapse" href="#mahachinSubmenu" class="sidebar-link collapsed">
                                mahachin data definitions
                            </a>
                            <ul id="mahachinSubmenu" class="sidebar-dropdown list-unstyled collapse">
                                <li class="sidebar-item"><a class="sidebar-link" href="#">machine profiles</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">production parameters</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">maintenance records</a></li>
                            </ul>
                        </li>

                        <!-- customer data definitions with submenu -->
                        <li class="sidebar-item">
                            <a data-bs-toggle="collapse" href="#customerSubmenu" class="sidebar-link collapsed">
                                customer data definitions
                            </a>
                            <ul id="customerSubmenu" class="sidebar-dropdown list-unstyled collapse">
                                <li class="sidebar-item"><a class="sidebar-link" href="#">customer demographics</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">contact information</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">loyalty & segments</a></li>
                            </ul>
                        </li>

                        <!-- products data definitions with submenu -->
                        <li class="sidebar-item">
                            <a data-bs-toggle="collapse" href="#productsSubmenu" class="sidebar-link collapsed">
                                products data definitions
                            </a>
                            <ul id="productsSubmenu" class="sidebar-dropdown list-unstyled collapse">
                                <li class="sidebar-item"><a class="sidebar-link" href="#">product attributes</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">pricing & tax rules</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">inventory metadata</a></li>
                            </ul>
                        </li>

                        <!-- dynamic forms with submenu -->
                        <li class="sidebar-item">
                            <a data-bs-toggle="collapse" href="#dynamicFormsSubmenu" class="sidebar-link collapsed">
                                dynamic forms
                            </a>
                            <ul id="dynamicFormsSubmenu" class="sidebar-dropdown list-unstyled collapse">
                                <li class="sidebar-item"><a class="sidebar-link" href="#">form builder schema</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">submission templates</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">validation rules</a></li>
                            </ul>
                        </li>

                        <!-- warehouse & vehicles with submenu -->
                        <li class="sidebar-item">
                            <a data-bs-toggle="collapse" href="#warehouseSubmenu" class="sidebar-link collapsed">
                                warehouse & vehicles
                            </a>
                            <ul id="warehouseSubmenu" class="sidebar-dropdown list-unstyled collapse">
                                <li class="sidebar-item"><a class="sidebar-link" href="#">storage zones</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">vehicle fleet data</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">logistics routes</a></li>
                            </ul>
                        </li>

                        <!-- mobile customization with submenu -->
                        <li class="sidebar-item">
                            <a data-bs-toggle="collapse" href="#mobileSubmenu" class="sidebar-link collapsed">
                                mobile customization
                            </a>
                            <ul id="mobileSubmenu" class="sidebar-dropdown list-unstyled collapse">
                                <li class="sidebar-item"><a class="sidebar-link" href="#">UI layouts</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">offline forms</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">push notification schemas</a></li>
                            </ul>
                        </li>

                        <!-- display units with submenu -->
                        <li class="sidebar-item">
                            <a data-bs-toggle="collapse" href="#displayUnitsSubmenu" class="sidebar-link collapsed">
                                display units
                            </a>
                            <ul id="displayUnitsSubmenu" class="sidebar-dropdown list-unstyled collapse">
                                <li class="sidebar-item"><a class="sidebar-link" href="#">screen layouts</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">widget definitions</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="#">real-time dashboards</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>


                    

					<li class="sidebar-header">
						Tools & Components
					</li>

					<li class="sidebar-item">
						<a class="sidebar-link" href="ui-buttons.html">
                        <i class="align-middle" data-feather="square"></i> <span class="align-middle">Buttons</span>
                        </a>
					</li>

					<li class="sidebar-item">
						<a class="sidebar-link" href="ui-forms.html">
              <i class="align-middle" data-feather="check-square"></i> <span class="align-middle">Forms</span>
            </a>
					</li>

					<li class="sidebar-item">
						<a class="sidebar-link" href="ui-cards.html">
              <i class="align-middle" data-feather="grid"></i> <span class="align-middle">Cards</span>
            </a>
					</li>

					<li class="sidebar-item">
						<a class="sidebar-link" href="ui-typography.html">
              <i class="align-middle" data-feather="align-left"></i> <span class="align-middle">Typography</span>
            </a>
					</li>

					<li class="sidebar-item">
						<a class="sidebar-link" href="icons-feather.html">
              <i class="align-middle" data-feather="coffee"></i> <span class="align-middle">Icons</span>
            </a>
					</li>

					<li class="sidebar-header">
						Plugins & Addons
					</li>

					<li class="sidebar-item">
						<a class="sidebar-link" href="charts-chartjs.html">
              <i class="align-middle" data-feather="bar-chart-2"></i> <span class="align-middle">Charts</span>
            </a>
					</li>

					<li class="sidebar-item">
						<a class="sidebar-link" href="maps-google.html">
              <i class="align-middle" data-feather="map"></i> <span class="align-middle">Maps</span>
            </a>
					</li>
				</ul>

			
			</div>
		</nav>