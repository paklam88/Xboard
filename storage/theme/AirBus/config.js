window.config={
    logo: '',
    title: "XBoard Demo",
    host: "/",  // same-origin API base; change if frontend is on another domain
    storeHome: {
        title: '选择最适合你的订阅计划',
        description: '若您已购买订阅且当前未过期，再次购买相同套餐将在原过期时间上追加时长，购买其他套餐则对原有订阅进行折抵'
    },
    SignPage: {
        title: '覆盖全球的高速网络，从这里开始你的旅程',
        desc1: 'High-speed network covering the whole world',
        desc2: 'Your journey starts here',
        inviteCodeEdit: false,
    },
    homeClient: {
        display: true,
        clients: [
            { key: 'windows', title: 'Windows 客户端', icon: 'ri:windows-fill', downloadLink: 'https://github.com/clash-verge-rev/clash-verge-rev/releases', knowledgeLink: '/dashboard/knowledge/4' },
            { key: 'mac', title: 'macOS 客户端', icon: 'ri:finder-fill', downloadLink: 'https://github.com/clash-verge-rev/clash-verge-rev/releases', knowledgeLink: '/dashboard/knowledge/4' },
            { key: 'android', title: 'Android 客户端', icon: 'ri:android-fill', downloadLink: 'https://github.com/MetaCubeX/ClashMetaForAndroid/releases', knowledgeLink: '/dashboard/knowledge/4' },
            { key: 'ios', title: 'iOS 客户端', icon: 'ri:apple-fill', downloadLink: 'https://apps.apple.com/', knowledgeLink: '/dashboard/knowledge/5' },
            { key: 'knowledge', title: '查看使用教程 / 知识库', icon: 'ri:questionnaire-fill', downloadLink: null, knowledgeLink: '/dashboard/knowledge' }
        ],
    },
    homeBanner: {
        display: true,
        title: '新手指南：注册 → 订阅 → 导入节点',
        link: '/dashboard/knowledge',
        btnText: '打开知识库',
        element: 'https://gd-hbimg.huaban.com/e68ceb12ffbd7dbeb698b3c5f4d2ed90185241be25183e-tShOmr_fw1200webp',
        bgImg: 'https://gd-hbimg.huaban.com/246ac6f06aeeab3b0c7e5908e7693b9a0e1b8a5249899-hB42f3_fw1200',
    }
}
