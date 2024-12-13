<?php

$default_list = [
    'third_parties' => [
        'analytics' => [
            'title'          => 'Analytics & Trackers',
            'items'          => [],
            'svg-icon' => 'analytics',
        ],
        'ad_networks' => [
            'title'          => 'Ad Networks',
            'items'          => [],
            'svg-icon'		 => 'ad_network',
        ],
        'payment_processors' => [
            'title'          => 'Payment Processors',
            'items'          => [],
            'svg-icon'		 => 'payment',
        ],
        'other_services' => [
            'title'          => 'Other Services',
            'items'          => [],
            'svg-icon'		 => 'plugins',
        ],
        'has_subcats'        => true,
    ],
    'wordpress' => [
        'themes'  => [
            'title'          => 'Themes',
            'items'          => [],
            'svg-icon'		 => 'themes',
        ],
        'plugins' => [
            'title'          => 'Plugins',
            'items'          => [],
            'svg-icon'		 => 'plugins',
        ],
        'has_subcats'        => true,
    ],
];

return [
    'shouldReturnAsExpectedIfAnalyticsIsEmpty' => [
        'config' => [
            'dynamic_lists' => (object) [
                'scripts' => (object) [
                    'analytics' => [],
                    'ad_networks' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'payment_processors' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'other_services' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                ],
                'themes' => [
                    'theme1' => (object) [
                        'title' => 'flatsome', 
                        'condition' => 'flatsome', 
                        'icon_url' => 'url_to_icon'
                    ],
                ],
                'plugins' => [
                    'plugin1' => (object) [
                        'title' => 'termly', 
                        'condition' => 'termly/termly.php', 
                        'icon_url' => 'url_to_icon'
                    ],
                ],
            ],
            'active_plugins' => [
                'termly/termly.php',
            ]
        ],
        'expected' => [
            'third_parties' => [
                'analytics' => [
                    'title'          => 'Analytics & Trackers',
                    'items'          => [],
                    'svg-icon' => 'analytics',
                ],
                'ad_networks' => [
                    'title'          => 'Ad Networks',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'ad_network',
                ],
                'payment_processors' => [
                    'title'          => 'Payment Processors',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'payment',
                ],
                'other_services' => [
                    'title'          => 'Other Services',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'others',
                ],
                'has_subcats'        => true,
            ],
            'wordpress' => [
                'themes'  => [
                    'title'          => 'Themes',
                    'items'          => [
                        [
                            'id' => 'theme1',
                            'title' => 'flatsome', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'themes',
                ],
                'plugins' => [
                    'title'          => 'Plugins',
                    'items'          => [
                        [
                            'id' => 'plugin1',
                            'title' => 'termly', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'plugins',
                ],
                'has_subcats'        => true,
            ],
        ],
    ],
    'shouldReturnAsExpectedIfAdNetworksIsEmpty' => [
        'config' => [
            'dynamic_lists' => (object) [
                'scripts' => (object) [
                    'analytics' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'ad_networks' => [],
                    'payment_processors' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'other_services' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                ],
                'themes' => [
                    'theme1' => (object) [
                        'title' => 'flatsome', 
                        'condition' => 'flatsome', 
                        'icon_url' => 'url_to_icon'
                    ],
                ],
                'plugins' => [
                    'plugin1' => (object) [
                        'title' => 'termly', 
                        'condition' => 'termly/termly.php', 
                        'icon_url' => 'url_to_icon'
                    ],
                ],
            ],
            'active_plugins' => [
                'termly/termly.php',
            ]
        ],
        'expected' => [
            'third_parties' => [
                'analytics' => [
                    'title'          => 'Analytics & Trackers',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'analytics',
                ],
                'ad_networks' => [
                    'title'          => 'Ad Networks',
                    'items'          => [],
                    'svg-icon'		 => 'ad_network',
                ],
                'payment_processors' => [
                    'title'          => 'Payment Processors',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'payment',
                ],
                'other_services' => [
                    'title'          => 'Other Services',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'others',
                ],
                'has_subcats'        => true,
            ],
            'wordpress' => [
                'themes'  => [
                    'title'          => 'Themes',
                    'items'          => [
                        [
                            'id' => 'theme1',
                            'title' => 'flatsome', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'themes',
                ],
                'plugins' => [
                    'title'          => 'Plugins',
                    'items'          => [
                        [
                            'id' => 'plugin1',
                            'title' => 'termly', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'plugins',
                ],
                'has_subcats'        => true,
            ],
        ],
    ],
    'shouldReturnAsExpectedIfPaymentProcessorsIsEmpty' => [
        'config' => [
            'dynamic_lists' => (object) [
                'scripts' => (object) [
                    'analytics' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'ad_networks' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'payment_processors' => [],
                    'other_services' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                ],
                'themes' => [
                    'theme1' => (object) [
                        'title' => 'flatsome', 
                        'condition' => 'flatsome', 
                        'icon_url' => 'url_to_icon'
                    ],
                ],
                'plugins' => [
                    'plugin1' => (object) [
                        'title' => 'termly', 
                        'condition' => 'termly/termly.php', 
                        'icon_url' => 'url_to_icon'
                    ],
                ],
            ],
            'active_plugins' => [
                'termly/termly.php',
            ]
        ],
        'expected' => [
            'third_parties' => [
                'analytics' => [
                    'title'          => 'Analytics & Trackers',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'analytics',
                ],
                'ad_networks' => [
                    'title'          => 'Ad Networks',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'ad_network',
                ],
                'payment_processors' => [
                    'title'          => 'Payment Processors',
                    'items'          => [],
                    'svg-icon'		 => 'payment',
                ],
                'other_services' => [
                    'title'          => 'Other Services',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'others',
                ],
                'has_subcats'        => true,
            ],
            'wordpress' => [
                'themes'  => [
                    'title'          => 'Themes',
                    'items'          => [
                        [
                            'id' => 'theme1',
                            'title' => 'flatsome', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'themes',
                ],
                'plugins' => [
                    'title'          => 'Plugins',
                    'items'          => [
                        [
                            'id' => 'plugin1',
                            'title' => 'termly', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'plugins',
                ],
                'has_subcats'        => true,
            ],
        ],
    ],
    'shouldReturnAsExpectedIfOtherServicesIsEmpty' => [
       'config' => [
            'dynamic_lists' => (object) [
                'scripts' => (object) [
                    'analytics' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'ad_networks' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'payment_processors' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'other_services' => [],
                ],
                'themes' => [
                    'theme1' => (object) [
                        'title' => 'flatsome', 
                        'condition' => 'flatsome', 
                        'icon_url' => 'url_to_icon'
                    ],
                ],
                'plugins' => [
                    'plugin1' => (object) [
                        'title' => 'termly', 
                        'condition' => 'termly/termly.php', 
                        'icon_url' => 'url_to_icon'
                    ],
                ],
            ],
            'active_plugins' => [
                'termly/termly.php',
            ]
        ],
        'expected' => [
            'third_parties' => [
                'analytics' => [
                    'title'          => 'Analytics & Trackers',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'analytics',
                ],
                'ad_networks' => [
                    'title'          => 'Ad Networks',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'ad_network',
                ],
                'payment_processors' => [
                    'title'          => 'Payment Processors',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'payment',
                ],
                'other_services' => [
                    'title'          => 'Other Services',
                    'items'          => [],
                    'svg-icon'		 => 'others',
                ],
                'has_subcats'        => true,
            ],
            'wordpress' => [
                'themes'  => [
                    'title'          => 'Themes',
                    'items'          => [
                        [
                            'id' => 'theme1',
                            'title' => 'flatsome', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'themes',
                ],
                'plugins' => [
                    'title'          => 'Plugins',
                    'items'          => [
                        [
                            'id' => 'plugin1',
                            'title' => 'termly', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'plugins',
                ],
                'has_subcats'        => true,
            ],
        ],
    ],
    'shouldReturnAsExpectedIfThemesIsEmpty' => [
        'config' => [
            'dynamic_lists' => (object) [
                'scripts' => (object) [
                    'analytics' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'ad_networks' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'payment_processors' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'other_services' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                ],
                'themes' => [],
                'plugins' => [
                    'plugin1' => (object) [
                        'title' => 'termly', 
                        'condition' => 'termly/termly.php', 
                        'icon_url' => 'url_to_icon'
                    ],
                ],
            ],
            'active_plugins' => [
                'termly/termly.php',
            ]
        ],
        'expected' => [
            'third_parties' => [
                'analytics' => [
                    'title'          => 'Analytics & Trackers',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'analytics',
                ],
                'ad_networks' => [
                    'title'          => 'Ad Networks',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'ad_network',
                ],
                'payment_processors' => [
                    'title'          => 'Payment Processors',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'payment',
                ],
                'other_services' => [
                    'title'          => 'Other Services',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'others',
                ],
                'has_subcats'        => true,
            ],
            'wordpress' => [
                'themes'  => [
                    'title'          => 'Themes',
                    'items'          => [],
                    'svg-icon'		 => 'themes',
                ],
                'plugins' => [
                    'title'          => 'Plugins',
                    'items'          => [
                        [
                            'id' => 'plugin1',
                            'title' => 'termly', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'plugins',
                ],  
                'has_subcats'        => true,
            ],
        ],
    ],
    'shouldReturnAsExpectedIfPluginsIsEmpty' => [
        'config' => [
            'dynamic_lists' => (object) [
                'scripts' =>  (object) [
                    'analytics' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'ad_networks' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'payment_processors' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                    'other_services' => [
                        'script1' => (object) [
                            'title' => 'Script One', 
                            'icon_url' => 'url_to_icon'
                        ],
                    ],
                ],
                'themes' => [
                    'theme1' => (object) [
                        'title' => 'flatsome', 
                        'condition' => 'flatsome', 
                        'icon_url' => 'url_to_icon'
                    ],
                ],
                'plugins' => [],
            ],
            'active_plugins' => [
                'termly/termly.php',
            ]
        ],
        'expected' => [
            'third_parties' => [
                'analytics' => [
                    'title'          => 'Analytics & Trackers',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'analytics',
                ],
                'ad_networks' => [
                    'title'          => 'Ad Networks',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'ad_network',
                ],
                'payment_processors' => [
                    'title'          => 'Payment Processors',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'payment',
                ],
                'other_services' => [
                    'title'          => 'Other Services',
                    'items'          => [
                        [
                            'id' => 'script1',
                            'title' => 'Script One', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'others',
                ],
                'has_subcats'        => true,
            ],
            'wordpress' => [
                'themes'  => [
                    'title'          => 'Themes',
                    'items'          => [
                        [
                            'id' => 'theme1',
                            'title' => 'flatsome', 
                            'icon' => 'url_to_icon'
                        ],
                    ],
                    'svg-icon'		 => 'themes',
                ],
                'plugins' => [
                    'title'          => 'Plugins',
                    'items'          => [],
                    'svg-icon'		 => 'plugins',
                ],
                'has_subcats'        => true,
            ],
        ],
    ],
];