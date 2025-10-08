<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index.php" class="logo logo-dark">
            <span class="logo-sm">
                <img src="assets/images/logo-sm.png" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="assets/images/logo-dark.png" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index.php" class="logo logo-light">
            <span class="logo-sm">
                <img src="assets/images/logo-sm.png" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="assets/images/logo-light.png" alt="" height="17">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span><?=$lang['t-menu']?></span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarDashboards" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarDashboards">
                        <i class="ri-dashboard-2-line"></i> <span><?=$lang['t-dashboards']?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarDashboards">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="dashboard-analytics.php" class="nav-link"><?=$lang['t-analytics']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="dashboard-crm.php" class="nav-link"><?=$lang['t-crm']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="index.php" class="nav-link"><?=$lang['t-ecommerce']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="dashboard-crypto.php" class="nav-link"><?=$lang['t-crypto']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="dashboard-projects.php" class="nav-link"><?=$lang['t-projects']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="dashboard-nft.php" class="nav-link" data-key="t-nft"> <?=$lang['t-nft']?></a>
                            </li>
                        </ul>
                    </div>
                </li> <!-- end Dashboard Menu -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarApps" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarApps">
                        <i class="ri-apps-2-line"></i> <span><?=$lang['t-apps']?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarApps">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="apps-calendar.php" class="nav-link"><?=$lang['t-calendar']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="apps-chat.php" class="nav-link"><?=$lang['t-chat']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarEmail" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarEmail" data-key="t-email">
                                    Email
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarEmail">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="apps-mailbox.php" class="nav-link"><?=$lang['t-mailbox']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#sidebaremailTemplates" class="nav-link" data-bs-toggle="collapse"
                                                role="button" aria-expanded="false"
                                                aria-controls="sidebaremailTemplates" data-key="t-email-templates">
                                                <?=$lang['t-email-templates']?>
                                            </a>
                                            <div class="collapse menu-dropdown" id="sidebaremailTemplates">
                                                <ul class="nav nav-sm flex-column">
                                                    <li class="nav-item">
                                                        <a href="apps-email-basic.php" class="nav-link"
                                                            data-key="t-basic-action"> <?=$lang['t-basic-action']?> </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="apps-email-ecommerce.php" class="nav-link"
                                                            data-key="t-ecommerce-action"> <?=$lang['t-ecommerce-action']?> </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarEcommerce" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarEcommerce"><?=$lang['t-ecommerce']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarEcommerce">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="apps-ecommerce-products.php"
                                                class="nav-link"><?=$lang['t-products']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-ecommerce-product-details.php"
                                                class="nav-link"><?=$lang['t-product-Details']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-ecommerce-add-product.php"
                                                class="nav-link"><?=$lang['t-create-product']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-ecommerce-orders.php"
                                                class="nav-link"><?=$lang['t-orders']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-ecommerce-order-details.php"
                                                class="nav-link"><?=$lang['t-order-details']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-ecommerce-customers.php"
                                                class="nav-link"><?=$lang['t-customers']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-ecommerce-cart.php"
                                                class="nav-link"><?=$lang['t-shopping-cart']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-ecommerce-checkout.php"
                                                class="nav-link"><?=$lang['t-checkout']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-ecommerce-sellers.php"
                                                class="nav-link"><?=$lang['t-sellers']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-ecommerce-seller-details.php"
                                                class="nav-link"><?=$lang['t-sellers-details']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarProjects" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarProjects"><?=$lang['t-projects']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarProjects">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="apps-projects-list.php" class="nav-link"><?=$lang['t-list']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-projects-overview.php"
                                                class="nav-link"><?=$lang['t-overview']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-projects-create.php"
                                                class="nav-link"><?=$lang['t-create-project']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarTasks" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarTasks"><?=$lang['t-tasks']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarTasks">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="apps-tasks-kanban.php"
                                                class="nav-link"><?=$lang['t-kanbanboard']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-tasks-list-view.php"
                                                class="nav-link"><?=$lang['t-list-view']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-tasks-details.php"
                                                class="nav-link"><?=$lang['t-task-details']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarCRM" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarCRM"><?=$lang['t-crm']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarCRM">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="apps-crm-contacts.php"
                                                class="nav-link"><?=$lang['t-contacts']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-crm-companies.php"
                                                class="nav-link"><?=$lang['t-companies']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-crm-deals.php" class="nav-link"><?=$lang['t-deals']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-crm-leads.php" class="nav-link"><?=$lang['t-leads']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarCrypto" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarCrypto"><?=$lang['t-crypto']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarCrypto">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="apps-crypto-transactions.php"
                                                class="nav-link"><?=$lang['t-transactions']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-crypto-buy-sell.php"
                                                class="nav-link"><?=$lang['t-buy-sell']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-crypto-orders.php" class="nav-link"><?=$lang['t-orders']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-crypto-wallet.php"
                                                class="nav-link"><?=$lang['t-my-wallet']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-crypto-ico.php" class="nav-link"><?=$lang['t-ico-list']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-crypto-kyc.php"
                                                class="nav-link"><?=$lang['t-kyc-application']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarInvoices" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarInvoices"><?=$lang['t-invoices']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarInvoices">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="apps-invoices-list.php"
                                                class="nav-link"><?=$lang['t-list-view']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-invoices-details.php"
                                                class="nav-link"><?=$lang['t-details']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-invoices-create.php"
                                                class="nav-link"><?=$lang['t-create-invoice']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarTickets" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarTickets"><?=$lang['t-supprt-tickets']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarTickets">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="apps-tickets-list.php"
                                                class="nav-link"><?=$lang['t-list-view']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-tickets-details.php"
                                                class="nav-link"><?=$lang['t-ticket-details']?></a>
                                        </li>

                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarnft" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarnft" data-key="t-nft-marketplace">
                                    <?=$lang['t-nft-marketplace']?> 
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarnft">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="apps-nft-marketplace.php" class="nav-link"
                                                data-key="t-marketplace"> <?=$lang['t-marketplace']?> </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-nft-explore.php" class="nav-link" data-key="t-explore-now">
                                            <?=$lang['t-explore-now']?> </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-nft-auction.php" class="nav-link" data-key="t-live-auction">
                                            <?=$lang['t-live-auction']?> </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-nft-item-details.php" class="nav-link"
                                                data-key="t-item-details"> <?=$lang['t-item-details']?> </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-nft-collections.php" class="nav-link"
                                                data-key="t-collections"> <?=$lang['t-collections']?> </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-nft-creators.php" class="nav-link" data-key="t-creators">
                                            <?=$lang['t-creators']?> </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-nft-ranking.php" class="nav-link" data-key="t-ranking">
                                            <?=$lang['t-ranking']?> </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-nft-wallet.php" class="nav-link" data-key="t-wallet-connect">
                                            <?=$lang['t-wallet-connect']?> </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="apps-nft-create.php" class="nav-link" data-key="t-create-nft">
                                            <?=$lang['t-create-nft']?> </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="apps-file-manager.php" class="nav-link"> <span data-key="t-file-manager"><?=$lang['t-file-manager']?></span> <span class="badge badge-pill bg-danger" data-key="t-new"><?=$lang['t-new']?></span></a>
                            </li>
                            <li class="nav-item">
                                <a href="apps-todo.php" class="nav-link"> <span data-key="t-to-do"><?=$lang['t-to-do']?></span> <span class="badge badge-pill bg-danger" data-key="t-new"><?=$lang['t-new']?></span></a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarLayouts" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarLayouts">
                        <i class="ri-layout-3-line"></i> <span><?=$lang['t-layouts']?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarLayouts">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="layouts-horizontal.php" target="_blank"
                                    class="nav-link"><?=$lang['t-horizontal']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="layouts-detached.php" target="_blank"
                                    class="nav-link"><?=$lang['t-detached']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="layouts-two-column.php" target="_blank"
                                    class="nav-link"><?=$lang['t-two-column']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="layouts-vertical-hovered.php" target="_blank"
                                    class="nav-link"><?=$lang['t-hovered']?></a>
                            </li>
                        </ul>
                    </div>
                </li> <!-- end Dashboard Menu -->

                <li class="menu-title"><i class="ri-more-fill"></i> <span><?=$lang['t-pages']?></span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarAuth" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarAuth">
                        <i class="ri-account-circle-line"></i> <span><?=$lang['t-authentication']?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarAuth">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="#sidebarSignIn" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarSignIn"><?=$lang['t-signin']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarSignIn">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="auth-signin-basic.php" class="nav-link"><?=$lang['t-basic']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="auth-signin-cover.php" class="nav-link"><?=$lang['t-cover']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarSignUp" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarSignUp"><?=$lang['t-signup']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarSignUp">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="auth-signup-basic.php" class="nav-link"><?=$lang['t-basic']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="auth-signup-cover.php" class="nav-link"><?=$lang['t-cover']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a href="#sidebarResetPass" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false"
                                    aria-controls="sidebarResetPass"><?=$lang['t-password-reset']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarResetPass">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="auth-pass-reset-basic.php"
                                                class="nav-link"><?=$lang['t-basic']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="auth-pass-reset-cover.php"
                                                class="nav-link"><?=$lang['t-cover']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a href="#sidebarchangePass" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarchangePass"
                                    data-key="t-password-create">
                                    Password-create
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarchangePass">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="auth-pass-change-basic.php" class="nav-link" data-key="t-basic">
                                            <?=$lang['t-basic']?> </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="auth-pass-change-cover.php" class="nav-link" data-key="t-cover">
                                            <?=$lang['t-cover']?> </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a href="#sidebarLockScreen" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarLockScreen"><?=$lang['t-lock-screen']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarLockScreen">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="auth-lockscreen-basic.php"
                                                class="nav-link"><?=$lang['t-basic']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="auth-lockscreen-cover.php"
                                                class="nav-link"><?=$lang['t-cover']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a href="#sidebarLogout" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarLogout"><?=$lang['t-logout']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarLogout">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="auth-logout-basic.php" class="nav-link"><?=$lang['t-basic']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="auth-logout-cover.php" class="nav-link"><?=$lang['t-cover']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarSuccessMsg" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false"
                                    aria-controls="sidebarSuccessMsg"><?=$lang['t-success-message']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarSuccessMsg">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="auth-success-msg-basic.php"
                                                class="nav-link"><?=$lang['t-basic']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="auth-success-msg-cover.php"
                                                class="nav-link"><?=$lang['t-cover']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarTwoStep" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false"
                                    aria-controls="sidebarTwoStep"><?=$lang['t-two-step-verification']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarTwoStep">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="auth-twostep-basic.php" class="nav-link"><?=$lang['t-basic']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="auth-twostep-cover.php" class="nav-link"><?=$lang['t-cover']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarErrors" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarErrors"><?=$lang['t-errors']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarErrors">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="auth-404-basic.php" class="nav-link"><?=$lang['t-404-basic']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="auth-404-cover.php" class="nav-link"><?=$lang['t-404-cover']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="auth-404-alt.php" class="nav-link"><?=$lang['t-404-alt']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="auth-500.php" class="nav-link"><?=$lang['t-500']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="auth-offline.php" class="nav-link" data-key="t-offline-page">
                                            Offline-page </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarPages" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarPages">
                        <i class="ri-pages-line"></i> <span><?=$lang['t-pages']?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarPages">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="pages-starter.php" class="nav-link"><?=$lang['t-starter']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarProfile" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarProfile"><?=$lang['t-profile']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarProfile">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="pages-profile.php" class="nav-link"><?=$lang['t-simple-page']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="pages-profile-settings.php"
                                                class="nav-link"><?=$lang['t-settings']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="pages-team.php" class="nav-link"><?=$lang['t-team']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="pages-timeline.php" class="nav-link"><?=$lang['t-timeline']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="pages-faqs.php" class="nav-link"><?=$lang['t-faqs']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="pages-pricing.php" class="nav-link"><?=$lang['t-pricing']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="pages-gallery.php" class="nav-link"><?=$lang['t-gallery']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="pages-maintenance.php" class="nav-link"><?=$lang['t-maintenance']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="pages-coming-soon.php" class="nav-link"><?=$lang['t-coming-soon']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="pages-sitemap.php" class="nav-link"><?=$lang['t-sitemap']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="pages-search-results.php" class="nav-link"><?=$lang['t-search-results']?></a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item"> <a class="nav-link menu-link" href="#sidebarLanding" data-bs-toggle="collapse"
                        role="button" aria-expanded="false" aria-controls="sidebarLanding">
                        <i class="ri-rocket-line"></i> <span data-key="t-landing"><?=$lang['t-landing']?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarLanding">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="landing.php" class="nav-link" data-key="t-one-page"> <?=$lang['t-one-page']?> </a>
                            </li>
                            <li class="nav-item">
                                <a href="nft-landing.php" class="nav-link" data-key="t-nft-landing">  <?=$lang['t-nft-landing']?> <span
                                        class="badge badge-pill bg-danger" data-key="t-new"> <?=$lang['t-new']?></span></a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="menu-title"><i class="ri-more-fill"></i> <span><?=$lang['t-components']?></span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarUI" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarUI">
                        <i class="ri-pencil-ruler-2-line"></i> <span><?=$lang['t-base-ui']?></span>
                    </a>
                    <div class="collapse menu-dropdown mega-dropdown-menu" id="sidebarUI">
                        <div class="row">
                            <div class="col-lg-4">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="ui-alerts.php" class="nav-link"><?=$lang['t-alerts']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-badges.php" class="nav-link"><?=$lang['t-badges']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-buttons.php" class="nav-link"><?=$lang['t-buttons']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-colors.php" class="nav-link"><?=$lang['t-colors']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-cards.php" class="nav-link"><?=$lang['t-cards']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-carousel.php" class="nav-link"><?=$lang['t-carousel']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-dropdowns.php" class="nav-link"><?=$lang['t-dropdowns']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-grid.php" class="nav-link"><?=$lang['t-grid']?></a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-lg-4">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="ui-images.php" class="nav-link"><?=$lang['t-images']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-tabs.php" class="nav-link"><?=$lang['t-tabs']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-accordions.php"
                                            class="nav-link"><?=$lang['t-accordion-collapse']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-modals.php" class="nav-link"><?=$lang['t-modals']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-offcanvas.php" class="nav-link"><?=$lang['t-offcanvas']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-placeholders.php" class="nav-link"><?=$lang['t-placeholders']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-progress.php" class="nav-link"><?=$lang['t-progress']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-notifications.php"
                                            class="nav-link"><?=$lang['t-notifications']?></a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-lg-4">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="ui-media.php" class="nav-link"><?=$lang['t-media-object']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-embed-video.php" class="nav-link"><?=$lang['t-embed-video']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-typography.php" class="nav-link"><?=$lang['t-typography']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-lists.php" class="nav-link"><?=$lang['t-lists']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-general.php" class="nav-link"><?=$lang['t-general']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-ribbons.php" class="nav-link"><?=$lang['t-ribbons']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ui-utilities.php" class="nav-link"><?=$lang['t-utilities']?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarAdvanceUI" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarAdvanceUI">
                        <i class="ri-stack-line"></i> <span><?=$lang['t-advance-ui']?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarAdvanceUI">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="advance-ui-sweetalerts.php" class="nav-link"><?=$lang['t-sweet-alerts']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="advance-ui-nestable.php" class="nav-link"><?=$lang['t-nestable-list']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="advance-ui-scrollbar.php" class="nav-link"><?=$lang['t-scrollbar']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="advance-ui-animation.php" class="nav-link"><?=$lang['t-animation']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="advance-ui-tour.php" class="nav-link"><?=$lang['t-tour']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="advance-ui-swiper.php" class="nav-link"><?=$lang['t-swiper-slider']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="advance-ui-ratings.php" class="nav-link"><?=$lang['t-ratings']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="advance-ui-highlight.php" class="nav-link"><?=$lang['t-highlight']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="advance-ui-scrollspy.php" class="nav-link"><?=$lang['t-scrollSpy']?></a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="widgets.php">
                        <i class="ri-honour-line"></i> <span><?=$lang['t-widgets']?></span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarForms" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarForms">
                        <i class="ri-file-list-3-line"></i> <span><?=$lang['t-forms']?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarForms">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="forms-elements.php" class="nav-link"><?=$lang['t-basic-elements']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="forms-select.php" class="nav-link"><?=$lang['t-form-select']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="forms-checkboxs-radios.php"
                                    class="nav-link"><?=$lang['t-checkboxs-radios']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="forms-pickers.php" class="nav-link"><?=$lang['t-pickers']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="forms-masks.php" class="nav-link"><?=$lang['t-input-masks']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="forms-advanced.php" class="nav-link"><?=$lang['t-advanced']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="forms-range-sliders.php" class="nav-link"><?=$lang['t-range-slider']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="forms-validation.php" class="nav-link"><?=$lang['t-validation']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="forms-wizard.php" class="nav-link"><?=$lang['t-wizard']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="forms-editors.php" class="nav-link"><?=$lang['t-editors']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="forms-file-uploads.php" class="nav-link"><?=$lang['t-file-uploads']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="forms-layouts.php" class="nav-link"><?=$lang['t-form-layouts']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="forms-select2.php" class="nav-link" data-key="t-select2"><?=$lang['t-select2']?> </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarTables" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarTables">
                        <i class="ri-layout-grid-line"></i> <span><?=$lang['t-tables']?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarTables">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="tables-basic.php" class="nav-link"><?=$lang['t-basic-tables']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="tables-gridjs.php" class="nav-link"><?=$lang['t-grid-js']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="tables-listjs.php" class="nav-link"><?=$lang['t-list-js']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="tables-datatables.php" class="nav-link" data-key="t-datatables"><?=$lang['t-datatables']?></a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarCharts" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarCharts">
                        <i class="ri-pie-chart-line"></i> <span><?=$lang['t-charts']?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarCharts">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="#sidebarApexcharts" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarApexcharts"><?=$lang['t-apexcharts']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarApexcharts">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="charts-apex-line.php" class="nav-link"><?=$lang['t-line']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-area.php" class="nav-link"><?=$lang['t-area']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-column.php" class="nav-link"><?=$lang['t-column']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-bar.php" class="nav-link"><?=$lang['t-bar']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-mixed.php" class="nav-link"><?=$lang['t-mixed']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-timeline.php"
                                                class="nav-link"><?=$lang['t-timeline']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-candlestick.php"
                                                class="nav-link"><?=$lang['t-candlstick']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-boxplot.php"
                                                class="nav-link"><?=$lang['t-boxplot']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-bubble.php" class="nav-link"><?=$lang['t-bubble']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-scatter.php"
                                                class="nav-link"><?=$lang['t-scatter']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-heatmap.php"
                                                class="nav-link"><?=$lang['t-heatmap']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-treemap.php"
                                                class="nav-link"><?=$lang['t-treemap']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-pie.php" class="nav-link"><?=$lang['t-pie']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-radialbar.php"
                                                class="nav-link"><?=$lang['t-radialbar']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-radar.php" class="nav-link"><?=$lang['t-radar']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="charts-apex-polar.php"
                                                class="nav-link"><?=$lang['t-polar-area']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="charts-chartjs.php" class="nav-link"><?=$lang['t-chartjs']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="charts-echarts.php" class="nav-link"><?=$lang['t-echarts']?></a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarIcons" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarIcons">
                        <i class="ri-compasses-2-line"></i> <span><?=$lang['t-icons']?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarIcons">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="icons-remix.php" class="nav-link"><?=$lang['t-remix']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="icons-boxicons.php" class="nav-link"><?=$lang['t-boxicons']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="icons-materialdesign.php" class="nav-link"><?=$lang['t-material-design']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="icons-lineawesome.php" class="nav-link"><?=$lang['t-line-awesome']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="icons-feather.php" class="nav-link"><?=$lang['t-feather']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="icons-crypto.php" class="nav-link"> <span data-key="t-crypto-svg"><?=$lang['t-crypto-svg']?></span> <span class="badge badge-pill bg-danger" data-key="t-new"><?=$lang['t-new']?></span></a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarMaps" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarMaps">
                        <i class="ri-map-pin-line"></i> <span><?=$lang['t-maps']?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarMaps">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="maps-google.php" class="nav-link"><?=$lang['t-google']?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="maps-vector.php" class="nav-link"><?=$lang['t-vector']?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="maps-leaflet.php" class="nav-link"><?=$lang['t-leaflet']?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarMultilevel" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarMultilevel">
                        <i class="ri-share-line"></i> <span><?=$lang['t-multi-level']?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarMultilevel">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="#" class="nav-link"><?=$lang['t-level-1.1']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="#sidebarAccount" class="nav-link" data-bs-toggle="collapse" role="button"
                                    aria-expanded="false" aria-controls="sidebarAccount"><?=$lang['t-level-1.2']?>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarAccount">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="#" class="nav-link"><?=$lang['t-level-2.1']?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#sidebarCrm" class="nav-link" data-bs-toggle="collapse"
                                                role="button" aria-expanded="false"
                                                aria-controls="sidebarCrm"><?=$lang['t-level-2.2']?>
                                            </a>
                                            <div class="collapse menu-dropdown" id="sidebarCrm">
                                                <ul class="nav nav-sm flex-column">
                                                    <li class="nav-item">
                                                        <a href="#" class="nav-link"><?=$lang['t-level-3.1']?></a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="#" class="nav-link"><?=$lang['t-level-3.2']?></a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>