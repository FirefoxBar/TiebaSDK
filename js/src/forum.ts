import { getClient, clientSign, fetchUrl, createUrl } from "./utils";
import md5 from "js-md5";

/**
 * 获取单个贴吧的信息
 * @access public
 * @param string $kw 贴吧名称
 * @return array
 */
export function getInfo(kw: string) {
  const data: { [x: string]: string } = {
    _client_id: getClient('_client_id'),
    _client_type: getClient('_client_type'),
    _client_version: getClient('_client_version'),
    _phone_imei: getClient('_phone_imei'),
    from: 'tieba',
    kw: kw,
    pn: '1',
    q_type: '2',
    rn: '30',
    with_group: '1'
  };
  data.sign = clientSign(data);
  return fetchUrl(createUrl('c/f/frs/page'), {
    post: data
  });
}
/**
 * 获取贴吧的fid
 * @access public
 * @param string $kw 贴吧名称
 * @return int
 */
const fid: { [x: string]: string } = {};
export async function getFid(kw: string) {
  const k = md5(kw);
  if (typeof fid[k] === 'undefined') {
    const tb = await getInfo(kw);
    fid[k] = tb.forum.id;
  }
  return fid[k];
}
/**
 * 获取某贴吧的贴子列表
 * @access public
 * @param string $kw 贴吧名称
 * @param int $page 页码
 * @return array
 */
export async function getThreadList(kw: string, page: number = 1) {
  const data: { [x: string]: string } = {
    _client_id: getClient('_client_id'),
    _client_type: getClient('_client_type'),
    _client_version: getClient('_client_version'),
    _phone_imei: getClient('_phone_imei'),
    from: 'tieba',
    kw: kw,
    pn: page.toString(),
    q_type: '2',
    rn: '30',
    with_group: '1'
  };
  data.sign = clientSign(data);
  const result = await fetchUrl(createUrl('c/f/frs/page'), {
    post: data
  });
  return result.thread_list;
}
/**
 * 获取贴子内容
 * @access public
 * @param int $kz 贴子ID
 * @param int $page 页码，当倒序时此参数无效
 * @return array
 */
export function getThread(kz: string, page: number = 1, last: boolean = false) {
  const data: { [x: string]: string } = {
    _client_id: getClient('_client_id'),
    _client_type: getClient('_client_type'),
    _client_version: getClient('_client_version'),
    _phone_imei: getClient('_phone_imei'),
    back: '0',
    kz: kz
  };
  if (last) {
    data.last = '1';
    data.net_type = getClient('net_type');
    data.q_type = '2';
    data.r = '1';
  } else {
    data.net_type = getClient('net_type');
    data.pn = `${page}`;
    data.q_type = '2';
  }
  data.rn = '30';
  data.timestamp = Date.now().toString();
  data.with_floor = '1';
  data.sign = clientSign(data);
  return fetchUrl(createUrl('c/f/pb/page'), {
    post: data
  });
}
/**
 * 获取楼中楼
 * @access public
 * @param int $pid 贴子ID
 * @param int $page 页码，当倒序时此参数无效
 * @return array
 */
export function getFloor(kz: string, pid: string, page: number = 1, last: boolean = false) {
  const data: { [x: string]: string } = {
    _client_id: getClient('_client_id'),
    _client_type: getClient('_client_type'),
    _client_version: getClient('_client_version'),
    _phone_imei: getClient('_phone_imei'),
    kz,
    pid,
    pn: `${page}`,
    timestamp: Date.now().toString()
  };
  data.sign = clientSign(data);
  return fetchUrl(createUrl('c/f/pb/floor'), {
    post: data
  });
}