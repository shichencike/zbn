<?php
// 设置JSON响应头
header("Content-Type: application/json");

// 数据文件路径
$filePath = 'members.txt';

// 初始化数据文件（如果不存在）
if (!file_exists($filePath)) {
    file_put_contents($filePath, json_encode([]));
}

// 读取数据
function getMembers() {
    global $filePath;
    $data = file_get_contents($filePath);
    return json_decode($data, true);
}

// 保存数据
function saveMembers($members) {
    global $filePath;
    return file_put_contents($filePath, json_encode($members, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// 处理请求
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    // 获取所有成员
    case 'GET':
        $members = getMembers();
        echo json_encode([
            'success' => true,
            'data' => $members
        ]);
        break;
        
    // 添加新成员
    case 'POST':
        $members = getMembers();
        $newMember = json_decode(file_get_contents('php://input'), true);
        
        // 生成唯一ID
        $newMember['id'] = uniqid();
        $newMember['createdAt'] = date('Y-m-d H:i:s');
        
        $members[] = $newMember;
        $result = saveMembers($members);
        
        echo json_encode([
            'success' => $result !== false,
            'data' => $newMember
        ]);
        break;
        
    // 删除成员
    case 'DELETE':
        $id = $_GET['id'];
        $members = getMembers();
        $filteredMembers = array_filter($members, function($member) use ($id) {
            return $member['id'] != $id;
        });
        
        $result = saveMembers(array_values($filteredMembers));
        
        echo json_encode([
            'success' => $result !== false
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => '不支持的请求方法'
        ]);
        break;
}
?>