import { forum } from './src';

// forum.getThread('5947069509').then(res => console.log(res.post_list[1]))
forum.getFloor('6776175808', '133088331588').then(res => console.log(res))