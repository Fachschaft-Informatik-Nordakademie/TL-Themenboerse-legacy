/* eslint-disable jsx-a11y/click-events-have-key-events */

import { User } from '../../../src/types/user';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../../../src/server/fetchUser';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import GradeOutlinedIcon from '@material-ui/icons/GradeOutlined';
import GradeIcon from '@material-ui/icons/Grade';
import {
  Button,
  Chip,
  Dialog,
  DialogActions,
  DialogContent,
  DialogContentText,
  DialogTitle,
  TextField,
  Tooltip,
  Typography,
} from '@material-ui/core';
import { useRouter } from 'next/router';
import React, { useEffect, useState } from 'react';
import { makeStyles } from '@material-ui/core/styles';
import { useTranslation } from 'next-i18next';
import Link from '../../../src/components/MaterialNextLink';
import { PageComponent } from '../../../src/types/PageComponent';
import { Topic } from '../../../src/types/topic';
import axiosClient from '../../../src/api';
import { red } from '@material-ui/core/colors';
import { Application } from '../../../src/types/application';

type Props = {
  user: User;
};

export async function getServerSideProps(context: GetServerSidePropsContext): Promise<GetServerSidePropsResult<Props>> {
  const user = await fetchUser(context.req.cookies);

  if (user === null) {
    return { redirect: { destination: '/login', permanent: false } };
  }

  return {
    props: { user, ...(await serverSideTranslations('de', ['topic', 'common'])) },
  };
}

const useStyles = makeStyles((theme) => ({
  section: {
    marginTop: theme.spacing(2),
  },
  tags: {
    marginLeft: theme.spacing(-1),
  },
  tagItem: { marginLeft: theme.spacing(1) },
  withdraw: {
    color: theme.palette.getContrastText(theme.palette.error.main),
    background: theme.palette.error.main,
  },
  applicationsWrapper: {
    marginTop: theme.spacing(6),
  },
  applicationsContentContainer: {
    marginTop: theme.spacing(2),
  },
  buttonDelete: {
    color: theme.palette.getContrastText(red[500]),
    backgroundColor: red[500],
    '&:hover': {
      backgroundColor: red[700],
    },
  },
}));

const TopicDetail: PageComponent<Props> = ({ user }: Props): JSX.Element => {
  const { t: tTopic } = useTranslation('topic');
  const { t: tCommon } = useTranslation('common');
  const router = useRouter();
  const classes = useStyles();
  const topicId = router.query.id as string;

  const [topic, setTopic] = useState<Topic>();
  const [loading, setLoading] = useState<boolean>(true);
  const [open, setOpen] = useState<boolean>(false);
  const [content, setContent] = useState<string>('');

  const [applicationAcceptLoading, setApplicationAcceptLoading] = useState<boolean>(false);

  const handleOpen = (): void => {
    setContent('');
    setOpen(true);
  };
  const handleClose = (): void => setOpen(false);
  const handleApplication = async (): Promise<void> => {
    try {
      await axiosClient.post('/application', { topic: topicId, content });
      await fetchTopic();
      handleClose();
    } catch (e) {
      console.log(e); // TOOD: How do we handle error messages?
    }
  };
  const handleWithdraw = async (): Promise<void> => {
    try {
      await axiosClient.delete(`/application/${topicId}`);
      await fetchTopic();
    } catch (e) {
      console.log(e);
    }
  };

  const fetchTopic = async (): Promise<void> => {
    setLoading(true);
    const response = await axiosClient.get('/topic/' + topicId);
    setTopic(response.data);
    setLoading(false);
  };

  const updateFollow = async (follow: boolean): Promise<void> => {
    const request = { favorite: follow };
    await axiosClient.put(`/topic/${topic.id}/favorite`, request);
    fetchTopic();
  };

  useEffect(() => {
    fetchTopic();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleAcceptApplication = React.useCallback(
    async (application: Application) => {
      setApplicationAcceptLoading(true);
      await axiosClient.put(`/application/${application.id}`);
      setApplicationAcceptLoading(false);
      fetchTopic();
    },
    [setApplicationAcceptLoading],
  );

  if (loading) {
    return <div>{tCommon('loading')}</div>;
  }

  const handleDelete = async (): Promise<void> => {
    const response = await axiosClient.delete('/topic/' + topicId);
    router.push('/');
  };

  const unFollow = (): void => {
    updateFollow(false);
  };

  const follow = (): void => {
    updateFollow(true);
  };

  const showApplyButton = topic.status === 'OPEN' && topic.author && user.id !== topic.author?.id;
  const canApply = showApplyButton && user.type === 'LDAP';

  const lengths = {
    application: 1000,
  };

  return (
    <div>
      <Typography gutterBottom variant="h4" component="h2">
        {tTopic('topic')} #{topicId}: {topic.title}
      </Typography>
      <div>
        <Typography gutterBottom variant="h5" component="h3">
          {tTopic('labelDescription')}
        </Typography>
        <Typography variant="body1" component="p">
          {topic.description}
        </Typography>
      </div>

      <div className={classes.section}>
        <Typography gutterBottom variant="h5" component="h3">
          {tTopic('labelRequirements')}
        </Typography>
        <Typography variant="body1" component="p">
          {topic.requirements}
        </Typography>
      </div>
      {topic.website && topic.website.length > 0 && (
        <div className={classes.section}>
          <Typography gutterBottom variant="h5" component="h3">
            {tTopic('labelWebsite')}
          </Typography>
          <Typography variant="body1" component="p">
            <a href={topic.website} target="_blank" rel="noopener noreferrer">
              {topic.website}
            </a>
          </Typography>
        </div>
      )}
      <div className={classes.section}>
        <Typography gutterBottom variant="h5" component="h3">
          {tTopic('labelPages')}
        </Typography>
        <Typography variant="body1" component="p">
          {topic.pages}
        </Typography>
      </div>
      <div className={classes.section}>
        <Typography gutterBottom variant="h5" component="h3">
          {tTopic('labelTags')}
        </Typography>
        <Typography variant="body1" component="div">
          <span className={classes.tags}>
            {topic.tags.map((t) => (
              <Chip classes={{ root: classes.tagItem }} key={t} label={t} />
            ))}
          </span>
        </Typography>
      </div>
      {showApplyButton && !topic.hasApplied && (
        <>
          <Tooltip title={canApply ? '' : tTopic('tooltipCisOnly')}>
            <span>
              <Button disabled={!canApply} variant="contained" color="primary" onClick={handleOpen}>
                {tTopic('buttonApply')}
              </Button>
            </span>
          </Tooltip>
          <Dialog open={open} onClose={handleClose} aria-labelledby="form-dialog-title">
            <DialogTitle id="form-dialog-title">{tTopic('applicationDialogTitle')}</DialogTitle>
            <DialogContent>
              <DialogContentText>{tTopic('applicationDialog')}</DialogContentText>
              <TextField
                margin="dense"
                id="name"
                label={tTopic('applicationContentLabel')}
                onChange={(e) => setContent(e.target.value)}
                type="text"
                fullWidth
                multiline
                rows={4}
                inputProps={{
                  maxLength: lengths.application,
                }}
                helperText={`${content.length}/${lengths.application}`}
              />
            </DialogContent>
            <DialogActions>
              <Button onClick={handleClose}>{tTopic('buttonCancel')}</Button>
              <Button onClick={handleApplication} variant="contained" color="primary">
                {tTopic('buttonApply')}
              </Button>
            </DialogActions>
          </Dialog>
        </>
      )}
      {showApplyButton && topic.hasApplied && (
        <Button variant="contained" className={classes.withdraw} onClick={handleWithdraw}>
          {tTopic('buttonWithdraw')}
        </Button>
      )}
      {((topic.author && user.id === topic.author.id) || user.admin) && (
        <Link href={`/topic/${topicId}/edit`}>
          <Button variant="contained" color="primary" type="submit">
            {tTopic('buttonEdit')}
          </Button>
        </Link>
      )}
      {user.admin && topic.status !== 'ASSIGNED' && (
        <Button color="secondary" onClick={handleDelete} className={classes.buttonDelete} variant="contained">
          {tTopic('buttonDelete')}
        </Button>
      )}
      {topic.author && user.id === topic.author.id && (
        <div className={classes.applicationsWrapper}>
          <Typography gutterBottom variant="h4" component="h2">
            {tTopic('applicationListTitle')}
          </Typography>
          <div>
            {topic.applications.map((application) => (
              <div key={application.id} className={classes.applicationsContentContainer}>
                <Link href={`/user/${application.candidate.id}`} variant="h6">
                  {application.candidate.profile.firstName} {application.candidate.profile.lastName}
                </Link>
                <Typography component="p" variant="body2">
                  {tTopic('applicationListLabelStatus')}: {application.status}
                </Typography>
                <Typography component="p" variant="body2" gutterBottom>
                  {application.content}
                </Typography>
                {application.status === 'OPEN' && (
                  <Button
                    variant="contained"
                    color="primary"
                    onClick={() => handleAcceptApplication(application)}
                    disabled={applicationAcceptLoading}
                  >
                    {tTopic('applicationListAcceptButton')}
                  </Button>
                )}
              </div>
            ))}
          </div>
        </div>
      )}

      {user.id !== topic.author.id && !topic.favorite && (
        // eslint-disable-next-line jsx-a11y/no-static-element-interactions
        <i onClick={() => follow()}>
          <Tooltip title="Thema merken">
            <GradeOutlinedIcon color="primary"></GradeOutlinedIcon>
          </Tooltip>
        </i>
      )}
      {user.id !== topic.author.id && topic.favorite && (
        // eslint-disable-next-line jsx-a11y/no-static-element-interactions
        <i onClick={() => unFollow()}>
          <Tooltip title="Thema nicht merken">
            <GradeIcon color="primary"></GradeIcon>
          </Tooltip>
        </i>
      )}
    </div>
  );
};

TopicDetail.layout = 'main';

export default TopicDetail;
